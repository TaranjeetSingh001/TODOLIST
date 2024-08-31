<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Todo List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h2 class="text-center mb-4">PHP - Simple To Do List App</h2>
                <div class="input-group mb-3">
                    <input type="text" id="taskInput" class="form-control" placeholder="Enter task">
                    <button class="btn btn-primary" onclick="addTask()">Add Task</button>
                </div>

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Task</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="taskList">

                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editTaskModal" tabindex="-1" aria-labelledby="editTaskModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editTaskModalLabel">Edit Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editTaskForm">
                        <div class="mb-3">
                            <label for="editTaskName" class="form-label">Task Name</label>
                            <input type="text" class="form-control" id="editTaskName" placeholder="Enter task name">
                        </div>
                        <div class="mb-3">
                            <label for="editTaskStatus" class="form-label">Status</label>
                            <select class="form-select" id="editTaskStatus">
                                <option value="0">Not Completed</option>
                                <option value="1">Completed</option>
                            </select>
                        </div>
                        <input type="hidden" id="editTaskId">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="updateTask()">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            fetchTasks();
        });

        function showAlert(message, type = 'success') {
            Swal.fire({
                icon: type,
                title: message,
                showConfirmButton: false,
                timer: 3000
            });
        }



        function fetchTasks() {
            axios.get('/gettasks')
                .then(response => {
                    const tasks = response.data;
                    showTasks(tasks);
                });
        }

        function showTasks(tasks) {
            const taskList = document.getElementById('taskList');
            taskList.innerHTML = '';
            tasks.forEach((task, index) => {
                const taskRow = `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${task.name}</td>
                       <td>
                        <span onclick="markAsComplete(${task.id})"
                            class="${task.is_completed ? 'text-success' : 'text-danger'}"
                            style="cursor: pointer;">
                            ${task.is_completed ? 'Completed' : 'Not Completed'}
                        </span>
                        </td>
                        <td>
                            <button class="btn btn-success btn-sm" onclick="editTask(${task.id})">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="deleteTask(${task.id})">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </td>
                    </tr>
                `;
                taskList.insertAdjacentHTML('beforeend', taskRow);
            });
        }

        function addTask() {
            const taskInput = document.getElementById('taskInput');
            const taskName = taskInput.value;

            axios.post('/tasks', {
                    name: taskName
                })
                .then(response => {
                    fetchTasks();
                    taskInput.value = '';
                    Swal.fire({
                    icon: 'success',
                    title: 'Task added successfully!',
                    showConfirmButton: false,
                    timer: 1500
                });
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed to add task!',
                        text: error.response.data.message || 'Task may already exist.',
                    });
                });
        }

        function editTask(id) {
            axios.get(`/tasks/${id}`)
                .then(response => {
                    const task = response.data;
                    document.getElementById('editTaskId').value = task.id;
                    document.getElementById('editTaskName').value = task.name;
                    document.getElementById('editTaskStatus').value = task.is_completed ? 1 : 0;

                    const editModal = new bootstrap.Modal(document.getElementById('editTaskModal'));
                    editModal.show();
                });
        }

        function updateTask() {
            const taskId = document.getElementById('editTaskId').value;
            const taskName = document.getElementById('editTaskName').value;
            const taskStatus = document.getElementById('editTaskStatus').value;

            axios.put(`/updatetasks/${taskId}`, {
                    name: taskName
                    , is_completed: taskStatus
                })
                .then(response => {
                    fetchTasks();
                    const editModal = bootstrap.Modal.getInstance(document.getElementById('editTaskModal'));
                    editModal.hide();
                    Swal.fire({
                        icon: 'success',
                        title: 'Task updated successfully!',
                        showConfirmButton: false,
                        timer: 1500
                    });
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed to update task!',
                        text: error.response.data.message || 'There was an issue updating the task.',
                    });
                });
        }

        function deleteTask(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You want to delete this task!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    axios.delete(`/tasks/${id}`)
                        .then(response => {
                            fetchTasks();
                            Swal.fire({
                                icon: 'success',
                                title: 'Task deleted successfully!',
                                showConfirmButton: false,
                                timer: 1500
                            });
                        })
                        .catch(error => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Failed to delete task!',
                                text: 'There was an issue deleting the task.',
                            });
                        });
                }
            });
        }

        function markAsComplete(id) {
            axios.put(`/tasks/${id}/complete`)
                .then(response => {
                    fetchTasks();
                    Swal.fire({
                        icon: 'success',
                        title: 'Task Status Updated!',
                        showConfirmButton: false,
                        timer: 1500
                    });
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed to mark task as complete!',
                        text: 'There was an issue updating the task status.',
                    });
                });
        }

    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
