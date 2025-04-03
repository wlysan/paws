/**
 * Product management JavaScript
 */
document.addEventListener('DOMContentLoaded', function() {
    // Carregar produtos na inicialização
    loadProducts();
    
    // Configurar filtros
    setupFilters();
    
    // Configurar manipulação de exclusão
    setupDeleteHandling();
});

/**
 * Carrega a lista de produtos via AJAX
 */
function loadProducts(page = 1, filters = {}) {
    const productsContainer = document.getElementById('products-container');
    const totalProductsCount = document.getElementById('total-products-count');
    
    // Mostrar indicador de carregamento
    productsContainer.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading products...</p>
        </div>
    `;
    
    // Construir a URL da consulta
    let url = '/index.php/api/products?action=list_products&page=' + page;
    
    // Adicionar filtros à URL se fornecidos
    if (filters.category) url += '&category_id=' + filters.category;
    if (filters.status) url += '&status=' + filters.status;
    if (filters.search) url += '&search=' + encodeURIComponent(filters.search);
    if (filters.price) url += '&price=' + filters.price;
    if (filters.sort) url += '&sort=' + filters.sort;
    
    // Fazer a requisição AJAX
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const products = data.data.products;
                const pagination = data.data.pagination;
                
                // Atualizar contagem de produtos
                totalProductsCount.textContent = pagination.total + ' Total Products';
                
                // Exibir produtos ou mensagem de vazio
                if (products.length > 0) {
                    renderProductsTable(products);
                    renderPagination(pagination);
                } else {
                    renderEmptyState();
                }
            } else {
                throw new Error(data.message || 'Failed to load products');
            }
        })
        .catch(error => {
            console.error('Error loading products:', error);
            productsContainer.innerHTML = `
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Error loading products: ${error.message}
                </div>
            `;
        });
}

/**
 * Renderiza a tabela de produtos
 */
function renderProductsTable(products) {
    const productsContainer = document.getElementById('products-container');
    
    let html = `
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th style="width: 80px;">Image</th>
                        <th>Product</th>
                        <th>SKU</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Categories</th>
                        <th>Status</th>
                        <th style="width: 180px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    products.forEach(product => {
        // Definir classes de status
        let statusClass = '';
        switch (product.status) {
            case 'published': statusClass = 'bg-success'; break;
            case 'draft': statusClass = 'bg-secondary'; break;
            case 'out_of_stock': statusClass = 'bg-danger'; break;
            case 'discontinued': statusClass = 'bg-dark'; break;
        }
        
        html += `
            <tr>
                <td>
                    ${product.primary_image ? 
                        `<img src="/${product.primary_image}" alt="${product.name}" class="product-thumb">` : 
                        `<div class="no-image">No image</div>`
                    }
                </td>
                <td>
                    <div class="fw-semibold">${product.name}</div>
                    <small class="text-muted">${product.short_description ? 
                        (product.short_description.length > 50 ? 
                            product.short_description.substring(0, 50) + '...' : 
                            product.short_description) : 
                        ''}
                    </small>
                </td>
                <td>${product.sku || 'N/A'}</td>
                <td>
                    <div class="fw-semibold">€${parseFloat(product.price).toFixed(2)}</div>
                    ${(product.sale_price && product.sale_price < product.price) ? 
                        `<small class="text-decoration-line-through text-muted">€${parseFloat(product.regular_price || product.price).toFixed(2)}</small>` : 
                        ''
                    }
                </td>
                <td>
                    ${product.stock_quantity <= 0 ? 
                        `<span class="badge bg-danger">Out of Stock</span>` : 
                        (product.stock_quantity <= 5 ? 
                            `<span class="badge bg-warning text-dark">Low: ${product.stock_quantity}</span>` : 
                            `<span class="badge bg-success">${product.stock_quantity}</span>`
                        )
                    }
                </td>
                <td>
                    ${product.category_names || '<span class="text-muted fst-italic">No categories</span>'}
                </td>
                <td>
                    <span class="badge ${statusClass}">
                        ${product.status.charAt(0).toUpperCase() + product.status.slice(1)}
                    </span>
                </td>
                <td>
                    <div class="btn-group" role="group">
                        <a href="/index.php/admin/products/edit/id/${product.id}" class="btn btn-sm btn-primary">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <button type="button" class="btn btn-sm btn-danger delete-product-btn" 
                                data-product-id="${product.id}" 
                                data-product-name="${product.name}">
                            <i class="fas fa-trash-alt"></i> Delete
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });
    
    html += `
                </tbody>
            </table>
        </div>
    `;
    
    productsContainer.innerHTML = html;
    
    // Configurar botões de exclusão
    document.querySelectorAll('.delete-product-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            const productName = this.getAttribute('data-product-name');
            showDeleteModal(productId, productName);
        });
    });
}

/**
 * Renderiza o estado vazio (sem produtos)
 */
function renderEmptyState() {
    const productsContainer = document.getElementById('products-container');
    
    productsContainer.innerHTML = `
        <div class="empty-state">
            <i class="fas fa-box-open"></i>
            <h4>No products found</h4>
            <p>Create your first product to get started or adjust your filters.</p>
            <div class="mt-3">
                <a href="/index.php/admin/products/add" class="btn btn-primary">
                    <i class="fas fa-plus-circle me-2"></i> Create First Product
                </a>
            </div>
        </div>
    `;
    
    // Esconde o container de paginação
    document.getElementById('pagination-container').innerHTML = '';
}

/**
 * Renderiza a paginação
 */
function renderPagination(pagination) {
    const paginationContainer = document.getElementById('pagination-container');
    
    if (pagination.last_page <= 1) {
        paginationContainer.innerHTML = '';
        return;
    }
    
    let html = `
        <nav aria-label="Products pagination">
            <ul class="pagination justify-content-center">
                <li class="page-item ${pagination.current_page <= 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${pagination.current_page - 1}">Previous</a>
                </li>
    `;
    
    for (let i = 1; i <= pagination.last_page; i++) {
        html += `
            <li class="page-item ${pagination.current_page == i ? 'active' : ''}">
                <a class="page-link" href="#" data-page="${i}">${i}</a>
            </li>
        `;
    }
    
    html += `
                <li class="page-item ${pagination.current_page >= pagination.last_page ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${pagination.current_page + 1}">Next</a>
                </li>
            </ul>
        </nav>
    `;
    
    paginationContainer.innerHTML = html;
    
    // Configurar eventos de clique na paginação
    document.querySelectorAll('#pagination-container .page-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const page = parseInt(this.getAttribute('data-page'));
            loadProducts(page, getFilters());
        });
    });
}

/**
 * Configura os filtros
 */
function setupFilters() {
    const applyFiltersBtn = document.getElementById('apply-filters');
    
    applyFiltersBtn.addEventListener('click', function() {
        loadProducts(1, getFilters());
    });
    
    // Permitir pressionar Enter no campo de pesquisa
    document.getElementById('filter_search').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            loadProducts(1, getFilters());
        }
    });
}

/**
 * Obtém os valores dos filtros
 */
function getFilters() {
    return {
        category: document.getElementById('filter_category').value,
        status: document.getElementById('filter_status').value,
        search: document.getElementById('filter_search').value,
        price: document.getElementById('filter_price').value,
        sort: 'newest' // Default sorting
    };
}

/**
 * Configura o tratamento de exclusão de produtos
 */
function setupDeleteHandling() {
    const deleteModal = document.getElementById('deleteProductModal');
    const deleteProductForm = document.getElementById('delete-product-form');
    const deleteProductId = document.getElementById('delete-product-id');
    const deleteProductName = document.getElementById('delete-product-name');
    
    // Nada a fazer se o modal não existe
    if (!deleteModal) return;
    
    // Atualiza o formulário quando o modal é exibido
    deleteProductForm.addEventListener('submit', function(e) {
        // O formulário já tem action e method configurados no HTML
    });
}

/**
 * Exibe o modal de confirmação de exclusão
 */
function showDeleteModal(productId, productName) {
    const deleteModal = document.getElementById('deleteProductModal');
    const deleteProductId = document.getElementById('delete-product-id');
    const deleteProductName = document.getElementById('delete-product-name');
    
    deleteProductId.value = productId;
    deleteProductName.textContent = productName;
    
    const bsModal = new bootstrap.Modal(deleteModal);
    bsModal.show();
}