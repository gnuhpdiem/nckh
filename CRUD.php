
<?php
session_start();
require("config.php");

// Hàm để lấy danh sách công việc cho một ngày cụ thể
function getTasks($conn, $userId, $date) {
    $stmt = $conn->prepare("SELECT * FROM tasks WHERE user_id = ? AND date = ?");
    $stmt->bind_param("is", $userId, $date);
    $stmt->execute();
    $result = $stmt->get_result();
    $tasks = [];
    while ($row = $result->fetch_assoc()) {
        $tasks[] = $row;
    }
    $stmt->close();
    return $tasks;
}

// Xử lý thêm công việc
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_task'])) {
    $userId = $_POST['user_id'];
    $date = $_POST['date'];
    $taskName = $_POST['task_name'];
    $startTime = $_POST['start_time'];
    $endTime = $_POST['end_time'];
    $priority = $_POST['priority'];
    $notes = $_POST['notes'];
    $progress = $_POST['progress'];

    $stmt = $conn->prepare("INSERT INTO tasks (user_id, date, task_name, start_time, end_time, priority, notes, progress) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssssi", $userId, $date, $taskName, $startTime, $endTime, $priority, $notes, $progress);
    $stmt->execute();
    $stmt->close();
}

// Xử lý sửa công việc
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_task'])) {
    $taskId = $_POST['task_id'];
    $taskName = $_POST['task_name'];
    $startTime = $_POST['start_time'];
    $endTime = $_POST['end_time'];
    $priority = $_POST['priority'];
    $notes = $_POST['notes'];
    $progress = $_POST['progress'];

    $stmt = $conn->prepare("UPDATE tasks SET task_name=?, start_time=?, end_time=?, priority=?, notes=?, progress=? WHERE id=?");
    $stmt->bind_param("sssssii", $taskName, $startTime, $endTime, $priority, $notes, $progress, $taskId);
    $stmt->execute();
    $stmt->close();
}

// Xử lý xóa công việc
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_task'])) {
    $taskId = $_POST['task_id'];

    $stmt = $conn->prepare("DELETE FROM tasks WHERE id=?");
    $stmt->bind_param("i", $taskId);
    $stmt->execute();
    $stmt->close();
}

// Lấy danh sách người dùng
$result = $conn->query("SELECT * FROM users");

// Giả sử user ID là 1 và lấy danh sách công việc cho ngày hôm nay
$userId = 1;
$date = date('Y-m-d');
$tasks = getTasks($conn, $userId, $date);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <title>User Management</title>
</head>
<body>
    <form action="index.php"><button class="btn btn-primary">return</button></form>
    <div class="container">
        <h1 class="mt-4">User Management</h1>

        <h2>Tasks for <?= $date ?></h2>
        <button class="btn btn-primary" onclick="showAddTaskForm()">Add Task</button>
        <table class="table table-bordered mt-4">
            <thead>
                <tr>
                    <th>Task</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Priority</th>
                    <th>Notes</th>
                    <th>Progress</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tasks as $task): ?>
                    <tr>
                        <td><?= $task['task_name'] ?></td>
                        <td><?= $task['start_time'] ?></td>
                        <td><?= $task['end_time'] ?></td>
                        <td><?= $task['priority'] ?></td>
                        <td><?= $task['notes'] ?></td>
                        <td><?= $task['progress'] ?>%</td>
                        <td>
                            <button class="btn btn-warning" onclick="editTask(<?= $task['id'] ?>, '<?= $task['task_name'] ?>', '<?= $task['start_time'] ?>', '<?= $task['end_time'] ?>', '<?= $task['priority'] ?>', '<?= $task['notes'] ?>', <?= $task['progress'] ?>)">Edit</button>
                            <form action="CRUD.php" method="post" style="display:inline;">
                                <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                                <button type="submit" name="delete_task" class="btn btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <form action="CRUD.php" method="post" id="addTaskForm" style="display:none;">
            <h2>Add Task</h2>
            <input type="hidden" name="user_id" value="<?= $userId ?>">
            <input type="hidden" name="date" value="<?= $date ?>">
            <div class="form-group">
                <label for="task_name">Task Name:</label>
                <input type="text" id="task_name" name="task_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="start_time">Start Time:</label>
                <input type="time" id="start_time" name="start_time" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="end_time">End Time:</label>
                <input type="time" id="end_time" name="end_time" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="priority">Priority:</label>
                <select id="priority" name="priority" class="form-control" required>
                    <option value="Thấp">Thấp</option>
                    <option value="Vừa">Vừa</option>
                    <option value="Cao">Cao</option>
                </select>
            </div>
            <div class="form-group">
                <label for="notes">Notes:</label>
                <textarea id="notes" name="notes" class="form-control"></textarea>
            </div>
            <div class="form-group">
                <label for="progress">Progress:</label>
                <input type="number" id="progress" name="progress" class="form-control" min="0" max="100" required>
            </div>
            <button type="submit" name="add_task" class="btn btn-primary">Add Task</button>
        </form>

        <form action="CRUD.php" method="post" id="editTaskForm" style="display:none;">
            <h2>Edit Task</h2>
            <input type="hidden" id="edit-task-id" name="task_id">
            <div class="form-group">
                <label for="edit-task_name">Task Name:</label>
                <input type="text" id="edit-task_name" name="task_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="edit-start_time">Start Time:</label>
                <input type="time" id="edit-start_time" name="start_time" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="edit-end_time">End Time:</label>
                <input type="time" id="edit-end_time" name="end_time" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="edit-priority">Priority:</label>
                <select id="edit-priority" name="priority" class="form-control" required>
                    <option value="Thấp">Thấp</option>
                    <option value="Vừa">Vừa</option>
                    <option value="Cao">Cao</option>
                </select>
            </div>
            <div class="form-group">
                <label for="edit-notes">Notes:</label>
                <textarea id="edit-notes" name="notes" class="form-control"></textarea>
            </div>
            <div class="form-group">
                <label for="edit-progress">Progress:</label>
                <input type="number" id="edit-progress" name="progress" class="form-control" min="0" max="100" required>
            </div>
            <button type="submit" name="edit_task" class="btn btn-primary">Save Changes</button>
        </form>
    </div>

    <script>
        function showAddTaskForm() {
            document.getElementById('addTaskForm').style.display = 'block';
            document.getElementById('editTaskForm').style.display = 'none';
        }

        function editTask(id, taskName, startTime, endTime, priority, notes, progress) {
            document.getElementById('edit-task-id').value = id;
            document.getElementById('edit-task_name').value = taskName;
            document.getElementById('edit-start_time').value = startTime;
            document.getElementById('edit-end_time').value = endTime;
            document.getElementById('edit-priority').value = priority;
            document.getElementById('edit-notes').value = notes;
            document.getElementById('edit-progress').value = progress;
            document.getElementById('addTaskForm').style.display = 'none';
            document.getElementById('editTaskForm').style.display = 'block';
        }
    </script>
</body>
</html>
