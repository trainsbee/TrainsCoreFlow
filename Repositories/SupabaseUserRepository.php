<?php 
require_once 'settings.php';
require_once 'db.php';

// Pass users data to JavaScript - we'll handle auth client-side
$users_json = json_encode($users);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestión de Pausas - Administrador</title>
  <link rel="stylesheet" href="assets/css/app.css">
  <script src="https://unpkg.com/feather-icons"></script>
<style>
  body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 20px;
  }
  
  .container {
    max-width: 1000px;
    margin: 0 auto;
  }
  
  table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
  }
  
  th, td {
    border: 1px solid #ddd;
    padding: 8px 12px;
    text-align: left;
  }
  
  th {
    background-color: #f2f2f2;
  }
  
  .btn {
    padding: 4px 8px;
    text-decoration: none;
    border: 1px solid #ccc;
    border-radius: 3px;
    cursor: pointer;
    font-size: 13px;
  }
  
  .modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
  }
  
  .modal-content {
    background: white;
    margin: 50px auto;
    padding: 20px;
    width: 90%;
    max-width: 800px;
    max-height: 80vh;
    overflow-y: auto;
  }
  
  .modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
  }
  
  .close-btn {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
  }
</style>
</head>
<body>
  <div class="container">
    <?php include 'partials/nav.php'; ?>
    
    <h1>Cargando...</h1>
    <p>Por favor espere...</p>
    
    <div style="margin: 20px 0;">
      <label for="start-date">Fecha de inicio:</label>
      <input type="date" id="start-date" value="<?php echo date('Y-m-d'); ?>">
      
      <label for="end-date" style="margin-left: 10px;">Fecha de fin:</label>
      <input type="date" id="end-date" value="<?php echo date('Y-m-d'); ?>">
      
      <button onclick="loadEmployees()" style="margin-left: 10px;">Filtrar</button>
    </div>
    
    <!-- Sección de Pausas Activas -->
    <div id="active-pauses-summary" style="margin: 20px 0;">
      <h2>Pausas Activas</h2>
      <div id="active-pauses-list">
        <p>Cargando pausas activas...</p>
      </div>
    </div>
    
    <h2>Empleados</h2>
    <table id="employees-table" style="width: 100%; border-collapse: collapse; margin-top: 10px;">
      <thead>
        <tr>
          <th style="text-align: left; padding: 8px; border-bottom: 1px solid #ddd;">Nombre</th>
          <th style="text-align: left; padding: 8px; border-bottom: 1px solid #ddd;">ID</th>
          <th style="text-align: center; padding: 8px; border-bottom: 1px solid #ddd;">Pausas Activas</th>
          <th style="text-align: center; padding: 8px; border-bottom: 1px solid #ddd;">Total Pausas</th>
          <th style="text-align: center; padding: 8px; border-bottom: 1px solid #ddd;">Tiempo Total</th>
          <th style="text-align: right; padding: 8px; border-bottom: 1px solid #ddd;">Acciones</th>
        </tr>
      </thead>
      <tbody id="employees-list">
        <tr>
          <td colspan="6" style="text-align: center; padding: 15px;">Cargando empleados...</td>
        </tr>
      </tbody>
    </table>
  </div>
  
  <!-- Pauses Modal -->
  <div id="pauses-modal" class="modal">
    <div class="modal-content" style="max-width: 800px; width: 90%;">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="margin: 0;">Pausas de <span id="employee-name"></span></h2>
        <button onclick="closeModal()" style="background: none; border: none; font-size: 24px; cursor: pointer;">&times;</button>
      </div>
      
      <!-- Resumen de Pausas -->
      <div id="pauses-summary" style="margin-bottom: 20px;">
        <p>Cargando resumen...</p>
      </div>
      
      <h3>Pausas Activas</h3>
      <div id="active-pauses">
        <p>Cargando pausas activas...</p>
      </div>
      
      <div style="margin-top: 20px;">
        <h3>Historial</h3>
        <div id="pauses-history">
          <p>Cargando historial de pausas...</p>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Global variables
    const users = <?php echo $users_json; ?>;
    let currentUser = null;
    let currentEmployeeId = '';
    
    // Check authentication
    function checkAuth() {
      const userData = localStorage.getItem('currentUser'); 
      if (!userData) {
        window.location.href = 'auth.php';
        return false;
      }
      
      try {
        currentUser = JSON.parse(userData);
        
        // Verify user exists in our database and is an admin
        let userFound = false;
        let isAdmin = currentUser.role === 'admin';
        
        // First check if user is a manager
        for (const managerId in users) {
          const manager = users[managerId];
          
          // Check if current user is this manager
          if (manager.id === currentUser.id) {
            userFound = true;
            // Add manager's employees to the currentUser object for easier access
            currentUser.employees = manager.employees || [];
            break;
          }
          
          // Check if current user is an employee of this manager
          const employee = manager.employees.find(emp => emp.id === currentUser.id);
          if (employee) {
            userFound = true;
            // Add manager's department to the employee
            currentUser.department = manager.DEPARTMENT || '';
            break;
          }
        }
        
        if (!userFound || !isAdmin) {
          window.location.href = 'dashboard.php';
          return false;
        }
        
        return true;
      } catch (e) {
        console.error('Error parsing user data:', e);
        window.location.href = 'auth.php';
        return false;
      }
    }
    
    // Logout function
    function logout() {
      localStorage.removeItem('currentUser');
      window.location.href = 'auth.php';
    }
    
    document.addEventListener('DOMContentLoaded', function() {
      if (checkAuth()) {
        // Update admin info in the UI
        document.querySelector('h1').textContent = `Gestión de Pausas - ${currentUser.DEPARTMENT || currentUser.department || 'Administración'}`;
        document.querySelector('p').textContent = `Usuario: ${currentUser.name} (${currentUser.role === 'admin' ? 'Administrador' : 'Empleado'})`;
        
        loadEmployees();
        feather.replace();
      }
    });
    
    async function loadEmployees() {
      const startDate = document.getElementById('start-date').value;
      const endDate = document.getElementById('end-date').value;
      
      // Get employees for the current manager
      let employees = [];
      
      // Find the manager's employees
      for (const managerId in users) {
        const manager = users[managerId];
        
        // If current user is this manager, get their employees
        if (manager.id === currentUser.id) {
          employees = manager.employees || [];
          break;
        }
        
        // If current user is an employee of this manager, get all employees from this manager
        if (manager.employees.some(e => e.id === currentUser.id)) {
          employees = manager.employees;
          currentUser.department = manager.DEPARTMENT || '';
          // Update the department in the UI
          document.querySelector('h1').textContent = `Gestión de Pausas - ${currentUser.department}`;
          break;
        }
      }
      
      const employeesList = document.getElementById('employees-list');
      employeesList.innerHTML = `
        <tr>
          <td colspan="6" style="text-align: center; padding: 15px;">Cargando estadísticas de pausas...</td>
        </tr>`;
      
      if (employees.length === 0) {
        employeesList.innerHTML = `
          <tr>
            <td colspan="6" style="text-align: center;">No hay empleados asignados.</td>
          </tr>`;
        document.getElementById('active-pauses-list').innerHTML = '<p>No hay empleados para mostrar.</p>';
        return;
      }
      
      try {
        // Get employee IDs
        const employeeIds = employees.map(e => e.id).join(',');
        
        // Fetch pause statistics for all employees ONCE
        const response = await fetch(`api/get_employee_stats.php?employee_ids=${employeeIds}&start_date=${startDate}&end_date=${endDate}`);
        const result = await response.json();
        
        if (!result.success) {
          throw new Error(result.message || 'Error al cargar estadísticas');
        }
        
        const stats = result.data || {};
        
        // Render active pauses summary using the fetched data
        renderActivePausesSummary(stats, employees);
        
        // Clear loading message and render employees table
        employeesList.innerHTML = '';
        
        // Create rows for each employee with their stats
        employees.forEach(employee => {
          const employeeStats = stats[employee.id] || {
            active_pauses: 0,
            total_pauses: 0,
            total_pause_time: '00:00:00'
          };
          
          const row = document.createElement('tr');
          row.innerHTML = `
            <td style="padding: 8px; border-bottom: 1px solid #eee;">${employee.name || 'N/A'}</td>
            <td style="padding: 8px; border-bottom: 1px solid #eee;">${employee.id || 'N/A'}</td>
            <td style="padding: 8px; border-bottom: 1px solid #eee; text-align: center; color: ${employeeStats.active_pauses > 0 ? '#dc3545' : '#28a745'}; font-weight: 500;">
              ${employeeStats.active_pauses}
            </td>
            <td style="padding: 8px; border-bottom: 1px solid #eee; text-align: center;">
              ${employeeStats.total_pauses}
            </td>
            <td style="padding: 8px; border-bottom: 1px solid #eee; text-align: center; font-family: monospace;">
              ${employeeStats.total_pause_time || '00:00:00'}
            </td>
            <td style="padding: 8px; border-bottom: 1px solid #eee; text-align: right;">
              <button class="btn" onclick="viewPauses('${employee.id}', '${(employee.name || '').replace(/'/g, "\\'")}')" style="padding: 4px 8px; font-size: 13px;">
                <i data-feather="eye" style="width: 14px; height: 14px;"></i> Ver
              </button>
            </td>
          `;
          employeesList.appendChild(row);
        });
        
      } catch (error) {
        console.error('Error loading employee stats:', error);
        employeesList.innerHTML = `
          <tr>
            <td colspan="6" style="text-align: center; color: #dc3545;">
              Error al cargar las estadísticas: ${error.message}
            </td>
          </tr>`;
        document.getElementById('active-pauses-list').innerHTML = '<p style="color: red;">Error al cargar las pausas activas.</p>';
      }
      
      feather.replace();
    }
    
    // New function to render active pauses summary using existing data
    function renderActivePausesSummary(stats, employees) {
      const activePausesList = document.getElementById('active-pauses-list');
      
      const employeesWithPauses = [];
      
      // Process the data
      for (const employeeId in stats) {
        const employeeStats = stats[employeeId];
        if (employeeStats.active_pauses > 0) {
          const employee = employees.find(e => e.id === employeeId);
          if (employee) {
            employeesWithPauses.push({
              ...employee,
              ...employeeStats
            });
          }
        }
      }
      
      // Display the active pauses summary
      if (employeesWithPauses.length === 0) {
        activePausesList.innerHTML = '<p>No hay pausas activas en este momento.</p>';
        return;
      }
      
      // Create table with active pauses
      let tableHTML = `
        <div style="overflow-x: auto;">
          <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
            <thead>
              <tr style="background-color: #f2f2f2;">
                <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">Empleado</th>
                <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">Departamento</th>
                <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">Hora de Inicio</th>
                <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">Tiempo Transcurrido</th>
                <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">Razón</th>
              </tr>
            </thead>
            <tbody>
      `;
      
      employeesWithPauses.forEach(emp => {
        if (emp.pauses && emp.pauses.length > 0) {
          emp.pauses.forEach((pause, index) => {
            const isFirst = index === 0;
            // Use display_time for showing the time, and start_time for calculations
            const displayTime = pause.display_time || (pause.start_time.includes('T') 
              ? pause.start_time.split('T')[1].split('.')[0] 
              : pause.start_time);
            
            tableHTML += `
              <tr style="border-bottom: 1px solid #eee;" 
                  onclick="viewPauses('${emp.id}', '${emp.name.replace(/'/g, "\\'")}')" 
                  style="cursor: pointer;" 
                  onmouseover="this.style.backgroundColor='#f9f9f9'" 
                  onmouseout="this.style.backgroundColor='transparent'">
                <td style="padding: 10px;${isFirst ? 'border-top: 1px solid #eee;' : ''}">${isFirst ? emp.name : ''}</td>
                <td style="padding: 10px;${isFirst ? 'border-top: 1px solid #eee;' : ''}">${isFirst ? (emp.department || 'N/A') : ''}</td>
                <td style="padding: 10px;${isFirst ? 'border-top: 1px solid #eee;' : ''}">${displayTime}</td>
                <td style="padding: 10px;${isFirst ? 'border-top: 1px solid #eee;' : ''}" class="elapsed-time" data-start="${pause.start_time}">00:00:00</td>
                <td style="padding: 10px;${isFirst ? 'border-top: 1px solid #eee;' : ''}">${pause.reason || 'Sin razón'}</td>
              </tr>
            `;
          });
        } else {
          tableHTML += `
            <tr style="border-bottom: 1px solid #eee;" 
                onclick="viewPauses('${emp.id}', '${emp.name.replace(/'/g, "\\'")}')" 
                style="cursor: pointer;" 
                onmouseover="this.style.backgroundColor='#f9f9f9'" 
                onmouseout="this.style.backgroundColor='transparent'">
              <td style="padding: 10px;">${emp.name}</td>
              <td style="padding: 10px;">${emp.department || 'N/A'}</td>
              <td style="padding: 10px;" colspan="3">Sin pausas activas</td>
            </tr>
          `;
        }
      });
      
      tableHTML += `
            </tbody>
          </table>
        </div>
      `;
      
      activePausesList.innerHTML = tableHTML;
    }
    
    function viewPauses(employeeId, employeeName) {
      currentEmployeeId = employeeId;
      document.getElementById('employee-name').textContent = employeeName;
      document.getElementById('pauses-modal').style.display = 'flex';
      
      // Clear previous content and show loading
      document.getElementById('pauses-summary').innerHTML = 'Cargando...';
      document.getElementById('active-pauses').innerHTML = '';
      document.getElementById('pauses-history').innerHTML = '';
      
      // Aquí puedes agregar una fetch específica para el historial del empleado individual si es necesario,
      // ya que el PHP actual no devuelve el historial detallado en la respuesta general.
      // Por ejemplo:
      // fetch(`api/get_employee_pauses.php?employee_id=${employeeId}&start_date=...&end_date=...`)
      // Pero como el código original no lo tiene implementado, lo dejo como está.
    }
    
    function closeModal() {
      document.getElementById('pauses-modal').style.display = 'none';
    }
    
    // Function to format time difference
    function formatDuration(startTime) {
      // Try to parse the start time as a date
      let start = new Date(startTime);
      
      // If parsing failed, try to handle it as a time-only string (HH:MM:SS)
      if (isNaN(start.getTime())) {
        const timeParts = startTime.match(/(\d{2}):(\d{2}):(\d{2})/);
        if (timeParts) {
          const now = new Date();
          start = new Date(
            now.getFullYear(),
            now.getMonth(),
            now.getDate(),
            parseInt(timeParts[1]),
            parseInt(timeParts[2]),
            parseInt(timeParts[3])
          );
          
          // If the calculated time is in the future, it's from yesterday
          if (start > now) {
            start.setDate(start.getDate() - 1);
          }
        }
      }
      
      const now = new Date();
      const diffMs = now - start;
      
      // Calculate hours, minutes, seconds
      const totalSeconds = Math.floor(diffMs / 1000);
      const hours = Math.floor(totalSeconds / 3600);
      const minutes = Math.floor((totalSeconds % 3600) / 60);
      const seconds = totalSeconds % 60;
      
      return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    }
    
    // Function to update elapsed times
    function updateElapsedTimes() {
      document.querySelectorAll('.elapsed-time').forEach(element => {
        const startTime = element.getAttribute('data-start');
        element.textContent = formatDuration(startTime);
      });
    }
    
    // Update elapsed times every second
    setInterval(updateElapsedTimes, 1000);
    
    // Initial update
    updateElapsedTimes();
    
    // Close modal when clicking outside the content
    window.onclick = function(event) {
      const modal = document.getElementById('pauses-modal');
      if (event.target === modal) {
        closeModal();
      }
    };
  </script>
  <script>
    feather.replace();
  </script>
</body>
</html>