<div class="space-y-6">
    <!-- Доступні категорії та швидкі дії -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg p-6 shadow">
            <h3 class="text-lg font-semibold mb-4 text-gray-900">📂 Доступні категорії</h3>
            
            <!-- Кнопка автогенерації -->
            <div class="mb-4 p-4 bg-blue-50 rounded-lg">
                <h4 class="font-medium text-blue-900 mb-2">Швидка настройка</h4>
                <button 
                    onclick="autoGenerateStructure()" 
                    class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 mr-2">
                    🔄 Автогенерація за категоріями
                </button>
                <span class="text-xs text-blue-700">Автоматично розподілить категорії по колонках</span>
            </div>
            
            <div class="space-y-2 max-h-80 overflow-y-auto">
                @foreach($availableCategories as $category)
                <div class="p-3 bg-gray-50 rounded border">
                    <div class="flex items-center justify-between mb-2">
                        <div>
                            <span class="font-medium text-sm">{{ $category->title }}</span>
                            <span class="text-xs text-gray-500 ml-2">({{ $category->children->count() }} підкатегорій)</span>
                        </div>
                        <button 
                            onclick="addCategoryColumn('{{ $category->title }}', {{ $category->id }})" 
                            class="bg-green-500 text-white px-3 py-1 rounded text-xs hover:bg-green-600">
                            + Додати колонку
                        </button>
                    </div>
                    
                    @if($category->children->isNotEmpty())
                    <div class="flex flex-wrap gap-1 mt-2">
                        @foreach($category->children as $child)
                        <button 
                            onclick="addSubcategoryToCurrentColumn('{{ $child->title }}', {{ $child->id }})" 
                            class="bg-gray-200 text-gray-700 px-2 py-1 rounded text-xs hover:bg-gray-300">
                            + {{ $child->title }}
                        </button>
                        @endforeach
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        
        <!-- Поточна структура мега-меню -->
        <div class="bg-white rounded-lg p-6 shadow">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">🏗️ Структура {{ $menuType === 'main' ? 'основного' : 'горизонтального' }} мега-меню</h3>
                <div class="flex space-x-2">
                    <button 
                        onclick="addCustomColumn()" 
                        class="bg-green-500 text-white px-3 py-2 rounded text-sm hover:bg-green-600">
                        + Колонка
                    </button>
                    <button 
                        onclick="openCustomLinkModal()" 
                        class="bg-purple-500 text-white px-3 py-2 rounded text-sm hover:bg-purple-600">
                        + Кастомний лінк
                    </button>
                </div>
            </div>
            
            <div id="megaMenuStructure" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Колонки будуть додаватись динамічно через JavaScript -->
                <div class="mega-column border-2 border-dashed border-gray-300 rounded p-4 min-h-32" data-column="0">
                    <h4 class="font-semibold mb-2">Колонка 1</h4>
                    <div class="sortable-area space-y-2" data-column="0"></div>
                </div>
                <div class="mega-column border-2 border-dashed border-gray-300 rounded p-4 min-h-32" data-column="1">
                    <h4 class="font-semibold mb-2">Колонка 2</h4>
                    <div class="sortable-area space-y-2" data-column="1"></div>
                </div>
                <div class="mega-column border-2 border-dashed border-gray-300 rounded p-4 min-h-32" data-column="2">
                    <h4 class="font-semibold mb-2">Колонка 3</h4>
                    <div class="sortable-area space-y-2" data-column="2"></div>
                </div>
            </div>
            
            <div class="mt-4 flex space-x-3">
                <button onclick="saveStructure()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    💾 Зберегти структуру
                </button>
                <button onclick="clearAll()" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                    🗑️ Очистити все
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal для кастомних лінків -->
<div id="customLinkModal" class="hidden fixed inset-0 z-50 bg-black bg-opacity-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">Додати кастомний лінк</h3>
        
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">Назва лінка</label>
                <input type="text" id="customLinkTitle" class="w-full border rounded px-3 py-2" placeholder="РОЗПРОДАЖ">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">URL</label>
                <input type="text" id="customLinkUrl" class="w-full border rounded px-3 py-2" placeholder="/sale">
            </div>
        </div>
        
        <div class="flex space-x-3 mt-6">
            <button onclick="saveCustomLink()" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                Додати
            </button>
            <button onclick="closeCustomLinkModal()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                Скасувати
            </button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
const menuType = '{{ $menuType }}';
let currentStructure = { columns: [] };

// Ініціалізація при завантаженні сторінки
document.addEventListener('DOMContentLoaded', function() {
    loadCurrentStructure();
    initializeSortable();
});

// Нові функції для простішої структури
function addCategoryColumn(categoryTitle, categoryId) {
    const columnIndex = currentStructure.columns.length;
    currentStructure.columns.push({
        title: categoryTitle,
        items: [],
        category_id: categoryId
    });
    
    renderStructure();
}

function addSubcategoryToCurrentColumn(subcategoryTitle, subcategoryId) {
    const lastColumnIndex = currentStructure.columns.length - 1;
    if (lastColumnIndex >= 0) {
        currentStructure.columns[lastColumnIndex].items.push(subcategoryTitle);
        renderStructure();
    } else {
        alert('Спочатку створіть колонку!');
    }
}

function autoGenerateStructure() {
    const categories = @json($availableCategories);
    
    currentStructure = {
        columns: []
    };
    
    categories.forEach(category => {
        const column = {
            title: category.title,
            items: category.children.map(child => child.title),
            category_id: category.id
        };
        currentStructure.columns.push(column);
    });
    
    renderStructure();
}

function addCustomColumn() {
    const columnIndex = currentStructure.columns.length;
    currentStructure.columns.push({
        title: `Колонка ${columnIndex + 1}`,
        items: []
    });
    
    renderStructure();
}

function addItemToColumn(columnIndex) {
    const title = prompt('Введіть назву елемента:');
    if (title) {
        if (!currentStructure.columns[columnIndex]) {
            currentStructure.columns[columnIndex] = {
                title: `Колонка ${columnIndex + 1}`,
                items: []
            };
        }
        currentStructure.columns[columnIndex].items.push(title);
        renderStructure();
    }
}

function findOptimalColumn() {
    if (currentStructure.columns.length === 0) {
        return 0;
    }
    
    let minItems = Infinity;
    let optimalColumn = 0;
    
    currentStructure.columns.forEach((column, index) => {
        if (column.items.length < minItems) {
            minItems = column.items.length;
            optimalColumn = index;
        }
    });
    
    return optimalColumn;
}

function removeItem(columnIndex, itemIndex) {
    currentStructure.columns[columnIndex].items.splice(itemIndex, 1);
    renderStructure();
}

function renderStructure() {
    const container = document.getElementById('megaMenuStructure');
    container.innerHTML = '';
    
    if (currentStructure.columns.length === 0) {
        container.innerHTML = '<div class="text-center text-gray-500 p-8">Немає колонок. Додайте категорії або натисніть автогенерацію.</div>';
        return;
    }
    
    currentStructure.columns.forEach((column, columnIndex) => {
        const columnDiv = document.createElement('div');
        columnDiv.className = 'mega-column border-2 border-gray-300 rounded p-4 min-h-40 bg-white hover:border-blue-400 transition-colors';
        columnDiv.setAttribute('data-column', columnIndex);
        
        const itemsHtml = column.items.map((item, itemIndex) => `
            <span class="inline-block bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs mr-1 mb-1 draggable" data-item="${itemIndex}">
                ${typeof item === 'string' ? item : item.title}
                <button onclick="removeItem(${columnIndex}, ${itemIndex})" class="ml-1 text-red-500 hover:text-red-700">×</button>
            </span>
        `).join('');
        
        const columnHtml = `
            <div class="flex items-center justify-between mb-3 pb-2 border-b">
                <h4 class="font-bold text-gray-800">${column.title}</h4>
                <button onclick="removeColumn(${columnIndex})" class="text-red-500 hover:text-red-700 text-lg">×</button>
            </div>
            <div class="sortable-area min-h-20" data-column="${columnIndex}">
                ${itemsHtml}
            </div>
            <button onclick="addItemToColumn(${columnIndex})" class="mt-2 text-xs text-blue-500 hover:text-blue-700">+ Додати елемент</button>
        `;
        
        columnDiv.innerHTML = columnHtml;
        container.appendChild(columnDiv);
    });
}

function removeColumn(columnIndex) {
    currentStructure.columns.splice(columnIndex, 1);
    renderStructure();
}

function openCustomLinkModal() {
    document.getElementById('customLinkModal').classList.remove('hidden');
}

function closeCustomLinkModal() {
    document.getElementById('customLinkModal').classList.add('hidden');
    document.getElementById('customLinkTitle').value = '';
    document.getElementById('customLinkUrl').value = '';
}

function saveCustomLink() {
    const title = document.getElementById('customLinkTitle').value;
    const url = document.getElementById('customLinkUrl').value;
    
    if (title && url) {
        const customItem = {
            type: 'custom',
            title: title,
            url: url
        };
        
        const columnIndex = findOptimalColumn();
        addItemToColumn(columnIndex, customItem);
        renderStructure();
        closeCustomLinkModal();
    }
}

function saveStructure() {
    // Відправити данні в Livewire
    @this.call('saveMegaMenuStructure', currentStructure, menuType);
}

function clearAll() {
    currentStructure = { columns: [] };
    renderStructure();
}

function loadCurrentStructure() {
    // Завантажити поточну структуру з бази
    const structureJson = @json($currentStructure);
    try {
        currentStructure = typeof structureJson === 'string' ? JSON.parse(structureJson) : structureJson;
        if (!currentStructure.columns) {
            currentStructure = { columns: [] };
        }
    } catch (e) {
        currentStructure = { columns: [] };
    }
    renderStructure();
}

function initializeSortable() {
    document.querySelectorAll('.sortable-area').forEach(area => {
        new Sortable(area, {
            group: 'mega-menu',
            animation: 150,
            ghostClass: 'sortable-ghost',
            onEnd: function (evt) {
                updateStructureFromDOM();
            }
        });
    });
}

function updateStructureFromDOM() {
    const columns = document.querySelectorAll('.mega-column');
    const newStructure = { columns: [] };
    
    columns.forEach((column, columnIndex) => {
        const columnTitle = column.querySelector('h4').textContent;
        const items = [];
        
        column.querySelectorAll('.draggable').forEach(item => {
            const itemIndex = parseInt(item.getAttribute('data-item'));
            if (currentStructure.columns[columnIndex] && currentStructure.columns[columnIndex].items[itemIndex]) {
                items.push(currentStructure.columns[columnIndex].items[itemIndex]);
            }
        });
        
        newStructure.columns.push({
            title: columnTitle,
            items: items
        });
    });
    
    currentStructure = newStructure;
}
</script>

<style>
.sortable-ghost {
    opacity: 0.4;
    background: #e5e7eb;
}

.draggable {
    cursor: move;
}

.draggable:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.mega-column {
    transition: all 0.2s ease;
}

.mega-column:hover {
    border-color: #3b82f6;
}
</style>