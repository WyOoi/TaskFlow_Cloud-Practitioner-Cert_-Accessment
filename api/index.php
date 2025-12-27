<?php
// Include database configuration
require_once __DIR__ . '/../config.php';

// PHP initialization
$pageTitle = "TaskFlow Pro";
$currentDate = date("F j, Y");
$author = "Weiyuan";

// Database setup - automatically create database and tables if they don't exist
try {
    // Create connection to MySQL (without database selected)
    $conn = new PDO("mysql:host=localhost", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if it doesn't exist
    $conn->exec("CREATE DATABASE IF NOT EXISTS $database");
    
    // Connect to the specific database
    $conn = new PDO("mysql:host=localhost;dbname=$database", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS $table (
        item_id INT(11) AUTO_INCREMENT PRIMARY KEY,
        content VARCHAR(255) NOT NULL,
        completed TINYINT(1) DEFAULT 0,
        priority VARCHAR(10) DEFAULT 'medium',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);
    
    // Check if table is empty and add sample data if needed
    $stmt = $conn->query("SELECT COUNT(*) FROM $table");
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        // Insert sample data
        $sample_tasks = [
            ["Complete project proposal", 1, "high"],
            ["Schedule team meeting", 0, "high"],
            ["Research market trends", 0, "medium"],
            ["Update client documentation", 1, "medium"],
            ["Review quarterly goals", 0, "low"]
        ];
        
        $stmt = $conn->prepare("INSERT INTO $table (content, completed, priority) VALUES (?, ?, ?)");
        foreach ($sample_tasks as $task) {
            $stmt->execute($task);
        }
    }
} catch(PDOException $e) {
    $setup_error = "Database setup error: " . $e->getMessage();
}

// Handle form submission for adding new tasks
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["action"]) && $_POST["action"] == "add" && !empty($_POST["new_task"])) {
        try {
            $db = new PDO("mysql:host=localhost;dbname=$database", $user, $password);
            $stmt = $db->prepare("INSERT INTO $table (content, completed, priority) VALUES (?, 0, ?)");
            $stmt->execute([$_POST["new_task"], $_POST["priority"]]);
            // Redirect to prevent form resubmission
            header("Location: " . $_SERVER["PHP_SELF"]);
            exit();
        } catch (PDOException $e) {
            $error = "Error adding task: " . $e->getMessage();
        }
    } elseif (isset($_POST["action"]) && $_POST["action"] == "toggle" && isset($_POST["task_id"])) {
        try {
            $db = new PDO("mysql:host=localhost;dbname=$database", $user, $password);
            // First get current status
            $stmt = $db->prepare("SELECT completed FROM $table WHERE item_id = ?");
            $stmt->execute([$_POST["task_id"]]);
            $current = $stmt->fetchColumn();
            
            // Toggle status
            $stmt = $db->prepare("UPDATE $table SET completed = ? WHERE item_id = ?");
            $stmt->execute([!$current, $_POST["task_id"]]);
            
            // Return success for AJAX
            if (isset($_POST["ajax"])) {
                echo json_encode(["success" => true, "new_status" => !$current]);
                exit;
            }
            
            // Redirect for non-AJAX
            header("Location: " . $_SERVER["PHP_SELF"]);
            exit();
        } catch (PDOException $e) {
            $error = "Error updating task: " . $e->getMessage();
            if (isset($_POST["ajax"])) {
                echo json_encode(["success" => false, "error" => $error]);
                exit;
            }
        }
    } elseif (isset($_POST["action"]) && $_POST["action"] == "delete" && isset($_POST["task_id"])) {
        try {
            $db = new PDO("mysql:host=localhost;dbname=$database", $user, $password);
            $stmt = $db->prepare("DELETE FROM $table WHERE item_id = ?");
            $stmt->execute([$_POST["task_id"]]);
            
            // Redirect
            header("Location: " . $_SERVER["PHP_SELF"]);
            exit();
        } catch (PDOException $e) {
            $error = "Error deleting task: " . $e->getMessage();
        }
    }
}

// Try to load tasks from database
$tasks = []; // Initialize empty array
try {
    $db = new PDO("mysql:host=localhost;dbname=$database", $user, $password);
    // Ensure we're using the correct column names as defined in the CREATE TABLE statement
    $stmt = $db->query("SELECT item_id as id, content, completed, priority, created_at FROM $table ORDER BY 
                        CASE 
                            WHEN priority = 'high' THEN 1 
                            WHEN priority = 'medium' THEN 2 
                            WHEN priority = 'low' THEN 3 
                        END, 
                        completed ASC, 
                        created_at DESC");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $tasks[] = [
            "id" => $row['id'],
            "task" => $row['content'],
            "completed" => (bool)$row['completed'],
            "priority" => $row['priority'],
            "created_at" => $row['created_at']
        ];
    }
} catch (PDOException $e) {
    $connection_error = "Database connection failed: " . $e->getMessage();
    // Will use empty tasks array
}

// Count statistics
$total_tasks = count($tasks);
$completed_tasks = 0;
$high_priority = 0;

foreach ($tasks as $task) {
    if ($task['completed']) $completed_tasks++;
    if ($task['priority'] == 'high') $high_priority++;
}

$completion_percentage = $total_tasks > 0 ? round(($completed_tasks / $total_tasks) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4895ef;
            --success-color: #4cc9f0;
            --danger-color: #f72585;
            --warning-color: #f8961e;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --gray-color: #adb5bd;
            --high-priority: #f72585;
            --medium-priority: #f8961e;
            --low-priority: #4cc9f0;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--dark-color);
            background-color: #f0f2f5;
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 250px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 20px;
            display: flex;
            flex-direction: column;
        }
        
        .sidebar-header {
            padding: 20px 0;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 20px;
        }
        
        .sidebar-header h1 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .sidebar-header p {
            font-size: 12px;
            opacity: 0.8;
        }
        
        .sidebar-menu {
            list-style: none;
            margin-top: 20px;
        }
        
        .sidebar-menu li {
            margin-bottom: 10px;
        }
        
        .sidebar-menu a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 10px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-menu i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .main-content {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
        }
        
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .dashboard-title h1 {
            font-size: 28px;
            font-weight: 700;
            color: var(--dark-color);
        }
        
        .dashboard-title p {
            color: var(--gray-color);
            font-size: 14px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
        }
        
        .user-info img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }
        
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 20px;
        }
        
        .stat-icon.blue {
            background-color: rgba(67, 97, 238, 0.1);
            color: var(--primary-color);
        }
        
        .stat-icon.green {
            background-color: rgba(76, 201, 240, 0.1);
            color: var(--success-color);
        }
        
        .stat-icon.red {
            background-color: rgba(247, 37, 133, 0.1);
            color: var(--danger-color);
        }
        
        .stat-info h3 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .stat-info p {
            color: var(--gray-color);
            font-size: 14px;
        }
        
        .progress-container {
            height: 6px;
            background-color: #e9ecef;
            border-radius: 3px;
            margin-top: 10px;
            overflow: hidden;
        }
        
        .progress-bar {
            height: 100%;
            background-color: var(--success-color);
        }
        
        .card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }
        
        .card-header {
            padding: 20px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-header h2 {
            font-size: 18px;
            font-weight: 600;
        }
        
        .card-body {
            padding: 0;
        }
        
        .task-list {
            list-style: none;
        }
        
        .task-item {
            padding: 15px 20px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            align-items: center;
            transition: background-color 0.3s;
        }
        
        .task-item:hover {
            background-color: #f8f9fa;
        }
        
        .task-item:last-child {
            border-bottom: none;
        }
        
        .task-item.completed {
            background-color: #f8f9fa;
        }
        
        .task-checkbox {
            margin-right: 15px;
            appearance: none;
            width: 20px;
            height: 20px;
            border: 2px solid var(--gray-color);
            border-radius: 50%;
            cursor: pointer;
            position: relative;
        }
        
        .task-checkbox:checked {
            border-color: var(--success-color);
            background-color: var(--success-color);
        }
        
        .task-checkbox:checked::after {
            content: '✓';
            position: absolute;
            color: white;
            font-size: 12px;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
        
        .task-content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .task-title {
            font-weight: 500;
            margin-bottom: 5px;
            transition: text-decoration 0.3s;
        }
        
        .completed .task-title {
            text-decoration: line-through;
            color: var(--gray-color);
        }
        
        .task-meta {
            display: flex;
            font-size: 12px;
            color: var(--gray-color);
        }
        
        .task-date {
            margin-right: 15px;
        }
        
        .task-priority {
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 500;
            text-transform: uppercase;
        }
        
        .priority-high {
            background-color: rgba(247, 37, 133, 0.1);
            color: var(--high-priority);
        }
        
        .priority-medium {
            background-color: rgba(248, 150, 30, 0.1);
            color: var(--medium-priority);
        }
        
        .priority-low {
            background-color: rgba(76, 201, 240, 0.1);
            color: var(--low-priority);
        }
        
        .task-actions {
            display: flex;
            opacity: 0;
            transition: opacity 0.3s;
        }
        
        .task-item:hover .task-actions {
            opacity: 1;
        }
        
        .task-action-btn {
            background: none;
            border: none;
            cursor: pointer;
            color: var(--gray-color);
            margin-left: 10px;
            transition: color 0.3s;
        }
        
        .task-action-btn:hover {
            color: var(--primary-color);
        }
        
        .task-action-btn.delete:hover {
            color: var(--danger-color);
        }
        
        .add-task {
            padding: 20px;
        }
        
        .add-task-form {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .form-group {
            flex: 1;
            min-width: 200px;
        }
        
        .form-control {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #e9ecef;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.3s;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
        }
        
        .error {
            color: var(--danger-color);
            padding: 10px;
            background-color: rgba(247, 37, 133, 0.1);
            border-radius: 5px;
            margin-bottom: 15px;
        }
        
        footer {
            text-align: center;
            font-size: 12px;
            color: var(--gray-color);
            padding: 20px 0;
            border-top: 1px solid #e9ecef;
            margin-top: 20px;
        }
        
        @media (max-width: 768px) {
            body {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                min-height: auto;
            }
            
            .stats-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h1>TaskFlow Pro</h1>
            <p>Task Management System</p>
        </div>
        
        <ul class="sidebar-menu">
            <li><a href="#" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="#"><i class="fas fa-tasks"></i> Tasks</a></li>
            <li><a href="#"><i class="fas fa-calendar"></i> Calendar</a></li>
            <li><a href="#"><i class="fas fa-chart-line"></i> Analytics</a></li>
            <li><a href="#"><i class="fas fa-cog"></i> Settings</a></li>
        </ul>
        
        <div style="margin-top: auto; padding: 20px 0; text-align: center; opacity: 0.7; font-size: 12px;">
            <p>Version 1.0.0</p>
        </div>
    </div>
    
    <div class="main-content">
        <div class="dashboard-header">
            <div class="dashboard-title">
                <h1>Dashboard</h1>
                <p>Welcome back! Here's your task overview.</p>
            </div>
            
            <div class="user-info">
                <img src="https://ui-avatars.com/api/?name=<?php echo $author; ?>&background=random" alt="User Avatar">
                <span><?php echo $author; ?></span>
            </div>
        </div>
        
        <?php if (isset($error) || isset($connection_error) || isset($setup_error)): ?>
            <div class="error">
                <?php 
                    if (isset($error)) echo $error;
                    if (isset($connection_error)) echo $connection_error;
                    if (isset($setup_error)) echo $setup_error;
                ?>
            </div>
        <?php endif; ?>
        
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fas fa-tasks"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $total_tasks; ?></h3>
                    <p>Total Tasks</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $completed_tasks; ?></h3>
                    <p>Completed Tasks</p>
                    <div class="progress-container">
                        <div class="progress-bar" style="width: <?php echo $completion_percentage; ?>%"></div>
                    </div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon red">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $high_priority; ?></h3>
                    <p>High Priority</p>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h2>Task Management</h2>
            </div>
            
            <div class="card-body">
                <ul class="task-list">
                    <?php if (empty($tasks)): ?>
                        <li class="task-item" style="justify-content: center; padding: 30px;">
                            <p>No tasks found. Add a new task below!</p>
                        </li>
                    <?php else: ?>
                        <?php foreach($tasks as $task): ?>
                            <li class="task-item <?php echo $task['completed'] ? 'completed' : ''; ?>">
                                <form action="" method="post" style="display: inline;">
                                    <input type="hidden" name="action" value="toggle">
                                    <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                    <input type="checkbox" class="task-checkbox" <?php echo $task['completed'] ? 'checked' : ''; ?> onChange="this.form.submit()">
                                </form>
                                
                                <div class="task-content">
                                    <div class="task-title"><?php echo htmlspecialchars($task['task']); ?></div>
                                    <div class="task-meta">
                                        <span class="task-date"><i class="far fa-calendar"></i> <?php echo date('M j, Y', strtotime($task['created_at'])); ?></span>
                                        <span class="task-priority priority-<?php echo $task['priority']; ?>"><?php echo $task['priority']; ?></span>
                                    </div>
                                </div>
                                
                                <div class="task-actions">
                                    <form action="" method="post" style="display: inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                        <button type="submit" class="task-action-btn delete" title="Delete task">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
                
                <div class="add-task">
                    <form class="add-task-form" action="" method="post">
                        <input type="hidden" name="action" value="add">
                        <div class="form-group">
                            <input type="text" name="new_task" class="form-control" placeholder="Add a new task..." required>
                        </div>
                        <div class="form-group" style="max-width: 150px;">
                            <select name="priority" class="form-control">
                                <option value="high">High Priority</option>
                                <option value="medium" selected>Medium Priority</option>
                                <option value="low">Low Priority</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Task</button>
                    </form>
                </div>
            </div>
        </div>
        
        <footer>
            <p>&copy; <?php echo date("Y"); ?> TaskFlow Pro by <?php echo $author; ?>. All rights reserved.</p>
        </footer>
    </div>
    
    <script>
        // Simple JavaScript for enhancing the UI
        document.addEventListener('DOMContentLoaded', function() {
            // Make checkboxes toggle task completion status via AJAX
            const checkboxes = document.querySelectorAll('.task-checkbox');
            
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    this.form.submit();
                });
            });
        });
    </script>
</body>
</html>
