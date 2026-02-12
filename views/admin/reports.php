<?php
$title = 'Relatórios de Eventos';
ob_start();
?>
<div class="row mb-4 animate-slide-down">
    <div class="col-12">
        <h2 class="fw-bold text-white"><i class="fas fa-chart-line me-2"></i>Relatórios de Eventos</h2>
        <p class="text-white-50">Visualize e filtre eventos para geração de relatórios.</p>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4 animate-slide-up">
    <div class="card-body p-4">
        <!-- Filtros -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <label class="form-label text-muted small fw-bold">Buscar Evento</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="searchInput" class="form-control border-start-0 ps-0" placeholder="Digite nome ou local (min. 3 caracteres)...">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted small fw-bold">De:</label>
                <input type="date" id="startDateInput" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted small fw-bold">Até:</label>
                <input type="date" id="endDateInput" class="form-control">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button id="clearFiltersBtn" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-eraser me-2"></i>Limpar
                </button>
            </div>
        </div>

        <!-- Tabela -->
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="reportsTable">
                <thead class="table-light">
                    <tr>
                        <th scope="col" class="sortable-header cursor-pointer" data-column="name" style="width: 40%;">
                            Nome do Evento <i class="fas fa-sort float-end mt-1 text-muted"></i>
                        </th>
                        <th scope="col" class="sortable-header cursor-pointer" data-column="date" style="width: 25%;">
                            Data <i class="fas fa-sort float-end mt-1 text-muted"></i>
                        </th>
                        <th scope="col" class="sortable-header cursor-pointer" data-column="location" style="width: 35%;">
                            Local <i class="fas fa-sort float-end mt-1 text-muted"></i>
                        </th>
                    </tr>
                </thead>
                <tbody id="reportsTableBody">
                    <!-- Conteúdo via AJAX -->
                    <tr>
                        <td colspan="3" class="text-center py-5 text-muted">
                            <div class="spinner-border text-primary mb-2" role="status"></div>
                            <p class="small mb-0">Carregando dados...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div id="noResultsMsg" class="text-center py-5 text-muted d-none">
            <i class="fas fa-folder-open fa-3x mb-3 opacity-50"></i>
            <p>Nenhum evento encontrado com os filtros atuais.</p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // State
    let currentFilters = {
        search: '',
        startDate: '',
        endDate: '',
        orderBy: 'date',
        orderDir: 'ASC'
    };

    // Elements
    const searchInput = document.getElementById('searchInput');
    const startDateInput = document.getElementById('startDateInput');
    const endDateInput = document.getElementById('endDateInput');
    const clearFiltersBtn = document.getElementById('clearFiltersBtn');
    const tableBody = document.getElementById('reportsTableBody');
    const noResultsMsg = document.getElementById('noResultsMsg');
    const sortHeaders = document.querySelectorAll('.sortable-header');

    // Debounce function for search
    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    // Fetch Data
    function fetchReports() {
        const params = new URLSearchParams(currentFilters);
        
        fetch(`/eventos/admin/apiReports?${params.toString()}`)
            .then(response => response.json())
            .then(data => {
                renderTable(data);
            })
            .catch(error => {
                console.error('Error fetching reports:', error);
                tableBody.innerHTML = '<tr><td colspan="3" class="text-center text-danger py-4">Erro ao carregar dados.</td></tr>';
            });
    }

    // Render Table
    function renderTable(data) {
        tableBody.innerHTML = '';
        
        if (data.length === 0) {
            tableBody.closest('table').classList.add('d-none');
            noResultsMsg.classList.remove('d-none');
            return;
        }
        
        tableBody.closest('table').classList.remove('d-none');
        noResultsMsg.classList.add('d-none');

        data.forEach(row => {
            const tr = document.createElement('tr');
            tr.style.cursor = 'pointer';
            tr.className = 'fade-in-row'; // Custom animation class if desired
            
            // Highlight name search match if needed, but simple text is fine
            tr.innerHTML = `
                <td class="fw-medium text-primary">${row.name}</td>
                <td><i class="far fa-clock me-2 text-muted"></i>${row.formatted_date}</td>
                <td><i class="fas fa-map-marker-alt me-2 text-muted"></i>${row.location_name || 'Local a definir'}</td>
            `;
            
            // Redirect on click
            tr.addEventListener('click', () => {
                window.location.href = row.detail_url;
            });

            // Hover effect handled by Bootstrap .table-hover
            
            tableBody.appendChild(tr);
        });
        
        updateHeaderIcons();
    }

    function updateHeaderIcons() {
        sortHeaders.forEach(th => {
            const icon = th.querySelector('i');
            icon.className = 'fas fa-sort float-end mt-1 text-muted'; // Reset
            
            if (th.dataset.column === currentFilters.orderBy) {
                if (currentFilters.orderDir === 'ASC') {
                    icon.className = 'fas fa-sort-up float-end mt-1 text-primary';
                } else {
                    icon.className = 'fas fa-sort-down float-end mt-1 text-primary';
                }
            }
        });
    }

    // Event Listeners
    
    // Search (Debounced)
    searchInput.addEventListener('input', debounce(function(e) {
        const val = e.target.value.trim();
        if (val.length >= 3 || val.length === 0) {
            currentFilters.search = val;
            fetchReports();
        }
    }, 500));

    // Date Filters
    startDateInput.addEventListener('change', function(e) {
        currentFilters.startDate = e.target.value;
        fetchReports();
    });

    endDateInput.addEventListener('change', function(e) {
        currentFilters.endDate = e.target.value;
        fetchReports();
    });

    // Clear Filters
    clearFiltersBtn.addEventListener('click', function() {
        searchInput.value = '';
        startDateInput.value = '';
        endDateInput.value = '';
        
        currentFilters.search = '';
        currentFilters.startDate = '';
        currentFilters.endDate = '';
        currentFilters.orderBy = 'date';
        currentFilters.orderDir = 'ASC';
        
        fetchReports();
    });

    // Ordering
    sortHeaders.forEach(th => {
        th.addEventListener('click', function() {
            const column = this.dataset.column;
            
            if (currentFilters.orderBy === column) {
                // Toggle direction
                currentFilters.orderDir = currentFilters.orderDir === 'ASC' ? 'DESC' : 'ASC';
            } else {
                currentFilters.orderBy = column;
                currentFilters.orderDir = 'ASC';
            }
            
            fetchReports();
        });
    });

    // Initial Fetch
    fetchReports();
});
</script>

<style>
    .cursor-pointer { cursor: pointer; }
    /* Subtle row animation */
    @keyframes fadeInRow {
        from { opacity: 0; transform: translateY(5px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .fade-in-row {
        animation: fadeInRow 0.3s ease forwards;
    }
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
