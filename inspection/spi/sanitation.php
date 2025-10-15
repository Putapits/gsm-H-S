<?php
// Inspector CRUD list for Health Inspections only
$types = ['health-inspection'];
$type = $types[0];
$typeLabel = 'Health Inspection';
try {
  $stmt = $db->prepare("SELECT * FROM service_requests WHERE service_type = ? ORDER BY created_at DESC");
  $stmt->execute([$type]);
  $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
  error_log('Error fetching SPI sanitation (inspector): ' . $e->getMessage());
  $requests = [];
}
function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>

<div class="mb-8">
  <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
    <div>
      <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Health Inspections (Inspector)</h2>
      <p class="mt-1 text-gray-600 dark:text-gray-400">Track and manage every sanitation inspection request from intake to completion.</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
      <div class="rounded-full border border-primary/30 bg-primary/10 px-4 py-2 text-sm font-semibold text-primary dark:border-primary/40 dark:bg-primary/15 dark:text-primary-200">
        Total Requests: <span class="ml-1"><?php echo number_format(count($requests)); ?></span>
      </div>
      <button id="spi-clear" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-600 transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700">Clear Filters</button>
      <button id="spi-export" class="inline-flex items-center rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100 focus:outline-none focus:ring-2 focus:ring-emerald-200 dark:border-emerald-500/40 dark:bg-emerald-500/20 dark:text-emerald-100 dark:hover:bg-emerald-500/30">Export CSV</button>
      <button id="spi-add" class="inline-flex items-center rounded-lg bg-primary px-3 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/40">Add New</button>
    </div>
  </div>
</div>

<section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-lg dark:border-slate-700 dark:bg-slate-900/70">
  <!-- Filters -->
  <div class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-4">
    <div>
      <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</label>
      <select id="spi-filter-status" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
        <option value="">All</option>
        <option value="pending">Pending</option>
        <option value="in_progress">In Progress</option>
        <option value="completed">Completed</option>
        <option value="cancelled">Cancelled</option>
      </select>
    </div>
    <div>
      <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Urgency</label>
      <select id="spi-filter-urgency" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
        <option value="">All</option>
        <option value="low">Low</option>
        <option value="medium">Medium</option>
        <option value="high">High</option>
        <option value="emergency">Emergency</option>
      </select>
    </div>
    <div class="md:col-span-2">
      <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Search</label>
      <div class="relative">
        <input id="spi-search" type="text" placeholder="Search name, email, phone, details..." class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2 pr-10 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
        <svg class="absolute right-3 top-2.5 h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
      </div>
    </div>
  </div>

  <div class="overflow-x-auto">
    <table class="min-w-full text-left text-sm text-gray-700 dark:text-gray-200" id="spi-table">
      <thead>
        <tr class="border-b border-gray-200 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:border-slate-700 dark:text-gray-400">
          <th class="py-3 px-3 cursor-pointer sortable" data-key="id">ID</th>
          <th class="py-3 px-3 cursor-pointer sortable" data-key="name">Full Name</th>
          <th class="py-3 px-3 cursor-pointer sortable" data-key="email">Email</th>
          <th class="py-3 px-3 cursor-pointer sortable" data-key="phone">Phone</th>
          <th class="py-3 px-3 cursor-pointer sortable" data-key="preferred_date">Preferred Date</th>
          <th class="py-3 px-3 cursor-pointer sortable" data-key="urgency">Urgency</th>
          <th class="py-3 px-3 cursor-pointer sortable" data-key="status">Status</th>
          <th class="py-3 px-3 cursor-pointer sortable" data-key="created">Created</th>
          <th class="py-3 px-3 text-right">Actions</th>
        </tr>
      </thead>
      <tbody id="spi-tbody">
        <?php if (empty($requests)): ?>
          <tr>
            <td colspan="9" class="py-6 px-3 text-center text-gray-500 dark:text-gray-400">No health inspection requests found.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($requests as $r): ?>
            <tr class="spi-row border-b border-gray-100 transition-colors hover:bg-gray-50 dark:border-slate-800 dark:hover:bg-slate-800/60"
                data-id="<?php echo h($r['id']); ?>"
                data-name="<?php echo h($r['full_name']); ?>"
                data-email="<?php echo h($r['email']); ?>"
                data-phone="<?php echo h($r['phone']); ?>"
                data-preferred_date="<?php echo h($r['preferred_date'] ?? ''); ?>"
                data-urgency="<?php echo h($r['urgency'] ?? ''); ?>"
                data-status="<?php echo h($r['status']); ?>"
                data-created="<?php echo h($r['created_at']); ?>"
                data-type="health-inspection"
                data-service="<?php echo h($typeLabel); ?>"
                data-address="<?php echo h($r['address']); ?>"
                data-details="<?php echo h($r['service_details']); ?>">
              <td class="py-3 px-3 font-medium text-gray-700 dark:text-gray-200">#<?php echo h($r['id']); ?></td>
              <td class="py-3 px-3 text-gray-600 dark:text-gray-300"><?php echo h($r['full_name']); ?></td>
              <td class="py-3 px-3 text-gray-600 dark:text-gray-300"><?php echo h($r['email']); ?></td>
              <td class="py-3 px-3 text-gray-600 dark:text-gray-300"><?php echo h($r['phone']); ?></td>
              <td class="py-3 px-3 text-gray-600 dark:text-gray-300"><?php echo h($r['preferred_date'] ?? ''); ?></td>
              <td class="py-3 px-3">
                <?php $u = strtolower($r['urgency'] ?? 'medium');
                  $urgencyClasses = [
                    'emergency' => 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-100',
                    'high' => 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-100',
                    'medium' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-200',
                    'low' => 'bg-slate-100 text-slate-700 dark:bg-slate-600/40 dark:text-slate-100',
                  ];
                  $urgencyClass = $urgencyClasses[$u] ?? 'bg-gray-200 text-gray-700 dark:bg-slate-700 dark:text-gray-100';
                ?>
                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold <?php echo $urgencyClass; ?>"><?php echo h(strtoupper($r['urgency'] ?? 'MEDIUM')); ?></span>
              </td>
              <td class="py-3 px-3">
                <?php $s = strtolower($r['status'] ?? 'pending');
                  $statusClasses = [
                    'completed' => 'bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-100',
                    'in_progress' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-100',
                    'cancelled' => 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-100',
                    'pending' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-200',
                  ];
                  $statusClass = $statusClasses[$s] ?? 'bg-gray-200 text-gray-700 dark:bg-slate-700 dark:text-gray-100';
                ?>
                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold <?php echo $statusClass; ?>"><?php echo h(strtoupper($r['status'] ?? 'PENDING')); ?></span>
              </td>
              <td class="py-3 px-3 text-gray-600 dark:text-gray-300"><?php echo h($r['created_at']); ?></td>
              <td class="py-3 px-3 text-right">
                <button class="mr-2 inline-flex items-center rounded-lg bg-primary px-3 py-1 text-xs font-semibold text-white shadow-sm transition hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/40" onclick="openViewModal(this.closest('tr'))">View</button>
                <button class="mr-2 inline-flex items-center rounded-lg bg-amber-500 px-3 py-1 text-xs font-semibold text-white shadow-sm transition hover:bg-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-400/60" onclick="openCrudModal('edit', this.closest('tr'))">Edit</button>
                <button class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-1 text-xs font-semibold text-gray-600 transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700" onclick="deleteRow(this.closest('tr'))">Delete</button>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <div id="spi-pagination" class="mt-6 flex items-center justify-end gap-2 text-sm text-gray-600 dark:text-gray-300"></div>
</section>

<!-- View Modal -->
<div id="spi-view-modal" class="fixed inset-0 hidden items-center justify-center z-50">
  <div class="absolute inset-0 bg-black/60" onclick="closeViewModal()"></div>
  <div class="relative modal-panel bg-dark-card border border-gray-600 rounded-lg max-w-3xl w-[92%] p-6 max-h-[85vh] overflow-y-auto">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-xl font-semibold text-white">Health Inspection Details</h3>
      <button class="text-gray-400 hover:text-white" onclick="closeViewModal()">✕</button>
    </div>
    <div id="spi-view-details" class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm"></div>
  </div>
  <style>#spi-view-modal.show{display:flex;}</style>
</div>

<!-- CRUD Modal (same as overview, with fixed type) -->
<div id="spi-crud-modal" class="fixed inset-0 hidden items-center justify-center z-50">
  <div class="absolute inset-0 bg-black/60" onclick="closeCrudModal()"></div>
  <div class="relative modal-panel bg-dark-card border border-gray-600 rounded-lg max-w-3xl w-[92%] p-6 max-h-[90vh] overflow-y-auto">
    <div class="flex items-center justify-between mb-4">
      <h3 id="crud-title" class="text-xl font-semibold text-white">Edit Health Inspection</h3>
      <button class="text-gray-400 hover:text-white" onclick="closeCrudModal()">✕</button>
    </div>
    <form id="crud-form" class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
      <input type="hidden" name="mode" value="edit" />
      <input type="hidden" name="id" />
      <input type="hidden" name="service_type" value="health-inspection" />

      <div>
        <label class="block text-xs text-gray-400 mb-1">User Email (for create)</label>
        <input name="user_email" type="email" placeholder="citizen@example.com" class="w-full bg-gray-700 text-white rounded px-3 py-2 border border-gray-600" />
      </div>
      <div>
        <label class="block text-xs text-gray-400 mb-1">Full Name</label>
        <input name="full_name" type="text" class="w-full bg-gray-700 text-white rounded px-3 py-2 border border-gray-600" />
      </div>
      <div>
        <label class="block text-xs text-gray-400 mb-1">Email</label>
        <input name="email" type="email" class="w-full bg-gray-700 text-white rounded px-3 py-2 border border-gray-600" />
      </div>
      <div>
        <label class="block text-xs text-gray-400 mb-1">Phone</label>
        <input name="phone" type="text" class="w-full bg-gray-700 text-white rounded px-3 py-2 border border-gray-600" />
      </div>
      <div class="md:col-span-2">
        <label class="block text-xs text-gray-400 mb-1">Address</label>
        <input name="address" type="text" class="w-full bg-gray-700 text-white rounded px-3 py-2 border border-gray-600" />
      </div>
      <div class="md:col-span-2">
        <label class="block text-xs text-gray-400 mb-1">Service Details</label>
        <textarea name="service_details" rows="4" class="w-full bg-gray-700 text-white rounded px-3 py-2 border border-gray-600"></textarea>
      </div>

      <div>
        <label class="block text-xs text-gray-400 mb-1">Preferred Date</label>
        <input name="preferred_date" type="date" class="w-full bg-gray-700 text-white rounded px-3 py-2 border border-gray-600" />
      </div>
      <div>
        <label class="block text-xs text-gray-400 mb-1">Urgency</label>
        <select name="urgency" class="w-full bg-gray-700 text-white rounded px-3 py-2 border border-gray-600">
          <option value="low">Low</option>
          <option value="medium">Medium</option>
          <option value="high">High</option>
          <option value="emergency">Emergency</option>
        </select>
      </div>
      <div>
        <label class="block text-xs text-gray-400 mb-1">Status</label>
        <select name="status" class="w-full bg-gray-700 text-white rounded px-3 py-2 border border-gray-600">
          <option value="pending">Pending</option>
          <option value="in_progress">In Progress</option>
          <option value="completed">Completed</option>
          <option value="cancelled">Cancelled</option>
        </select>
      </div>

      <div class="md:col-span-2 flex justify-end gap-2 pt-2">
        <button type="button" class="px-3 py-2 border border-gray-600 text-gray-300 rounded" onclick="closeCrudModal()">Cancel</button>
        <button type="submit" class="px-3 py-2 bg-primary text-white rounded">Save</button>
      </div>
    </form>
  </div>
  <style>#spi-crud-modal.show { display:flex; }</style>
</div>

<style>
  .modal-field { background-color: rgba(55,65,81,0.4); border-color: #4b5563; }
  /* Light mode overrides for modal readability */
  @media (prefers-color-scheme: light) {
    .modal-panel { background-color: #ffffff !important; color: #111827 !important; border-color: #d1d5db !important; }
    .modal-panel h3 { color: #111827 !important; }
    .modal-panel .text-white { color: #111827 !important; }
    .modal-panel .text-gray-400 { color: #374151 !important; }
    .modal-panel .text-gray-300 { color: #374151 !important; }
    .modal-panel .border-gray-600 { border-color: #d1d5db !important; }
    .modal-panel input,
    .modal-panel select,
    .modal-panel textarea { background-color: #ffffff !important; color: #111827 !important; border-color: #d1d5db !important; }
    .modal-field { background-color: #f9fafb !important; border-color: #e5e7eb !important; color: #111827 !important; }
  }
  /* Support explicit light theme toggles */
  body.light .modal-panel,
  body[data-theme="light"] .modal-panel { background-color: #ffffff !important; color: #111827 !important; border-color: #d1d5db !important; }
  body.light .modal-panel h3,
  body[data-theme="light"] .modal-panel h3 { color: #111827 !important; }
  body.light .modal-panel .text-white,
  body[data-theme="light"] .modal-panel .text-white { color: #111827 !important; }
  body.light .modal-panel .text-gray-400,
  body[data-theme="light"] .modal-panel .text-gray-400 { color: #374151 !important; }
  body.light .modal-panel .text-gray-300,
  body[data-theme="light"] .modal-panel .text-gray-300 { color: #374151 !important; }
  body.light .modal-panel .border-gray-600,
  body[data-theme="light"] .modal-panel .border-gray-600 { border-color: #d1d5db !important; }
  body.light .modal-panel input,
  body[data-theme="light"] .modal-panel input,
  body.light .modal-panel select,
  body[data-theme="light"] .modal-panel select,
  body.light .modal-panel textarea,
  body[data-theme="light"] .modal-panel textarea { background-color: #ffffff !important; color: #111827 !important; border-color: #d1d5db !important; }
  body.light .modal-field,
  body[data-theme="light"] .modal-field { background-color: #f9fafb !important; border-color: #e5e7eb !important; color: #111827 !important; }
</style>

<script>
  const spiBody = document.getElementById('spi-tbody');
  const spiSearch = document.getElementById('spi-search');
  const spiFilterStatus = document.getElementById('spi-filter-status');
  const spiFilterUrgency = document.getElementById('spi-filter-urgency');
  const spiTable = document.getElementById('spi-table');
  const spiExport = document.getElementById('spi-export');
  const spiPagination = document.getElementById('spi-pagination');
  const spiAdd = document.getElementById('spi-add');
  let spiSortKey = 'id';
  let spiSortAsc = false;
  let spiPage = 1;
  const spiPageSize = 10;

  function spiRows(){ return Array.from(spiBody.querySelectorAll('.spi-row')); }

  function applySpiFilters() {
    const q = (spiSearch?.value || '').toLowerCase();
    const fs = (spiFilterStatus?.value || '').toLowerCase();
    const fu = (spiFilterUrgency?.value || '').toLowerCase();
    if (!spiBody) return;
    let rows = spiRows();
    rows.forEach(tr => {
      const text = tr.innerText.toLowerCase();
      const s = (tr.getAttribute('data-status')||'').toLowerCase();
      const u = (tr.getAttribute('data-urgency')||'').toLowerCase();
      tr.dataset._match = (text.includes(q) && (!fs || s===fs) && (!fu || u===fu)) ? '1' : '0';
    });
    rows = rows.filter(tr => tr.dataset._match === '1');
    rows.sort((a,b)=>{
      const ka = (a.dataset[spiSortKey]||'').toLowerCase();
      const kb = (b.dataset[spiSortKey]||'').toLowerCase();
      if (ka < kb) return spiSortAsc ? -1 : 1;
      if (ka > kb) return spiSortAsc ? 1 : -1;
      return 0;
    });
    const total = rows.length;
    const pages = Math.max(1, Math.ceil(total / spiPageSize));
    if (spiPage > pages) spiPage = pages;
    const start = (spiPage - 1) * spiPageSize;
    const end = start + spiPageSize;
    const visible = new Set(rows.slice(start, end));
    spiRows().forEach(tr => tr.style.display = 'none');
    visible.forEach(tr => tr.style.display = '');
    renderSpiPagination(pages);
  }

  function renderSpiPagination(pages){
    if (!spiPagination) return;
    spiPagination.innerHTML = '';
    const prev = document.createElement('button');
    prev.textContent = 'Prev';
    prev.className = 'px-2 py-1 bg-gray-700 hover:bg-gray-600 text-white rounded';
    prev.disabled = spiPage <= 1;
    prev.onclick = () => { spiPage--; applySpiFilters(); };
    spiPagination.appendChild(prev);
    for(let i=1;i<=pages;i++){
      const b = document.createElement('button');
      b.textContent = i;
      b.className = 'px-2 py-1 rounded ' + (i===spiPage ? 'bg-primary text-white' : 'bg-gray-700 hover:bg-gray-600 text-white');
      b.onclick = () => { spiPage = i; applySpiFilters(); };
      spiPagination.appendChild(b);
    }
    const next = document.createElement('button');
    next.textContent = 'Next';
    next.className = 'px-2 py-1 bg-gray-700 hover:bg-gray-600 text-white rounded';
    next.disabled = spiPage >= pages;
    next.onclick = () => { spiPage++; applySpiFilters(); };
    spiPagination.appendChild(next);
  }

  [spiSearch, spiFilterStatus, spiFilterUrgency].forEach(el => {
    if (el) el.addEventListener('input', applySpiFilters);
    if (el) el.addEventListener('change', applySpiFilters);
  });
  const spiClearBtn = document.getElementById('spi-clear');
  if (spiClearBtn){ spiClearBtn.addEventListener('click', function(){ if(spiSearch) spiSearch.value=''; if(spiFilterStatus) spiFilterStatus.value=''; if(spiFilterUrgency) spiFilterUrgency.value=''; spiPage=1; applySpiFilters(); }); }

  if (spiTable) {
    spiTable.querySelectorAll('th.sortable').forEach(th => {
      th.addEventListener('click', () => {
        const key = th.getAttribute('data-key');
        if (spiSortKey === key) { spiSortAsc = !spiSortAsc; } else { spiSortKey = key; spiSortAsc = true; }
        applySpiFilters();
      });
    });
  }

  function download(filename, text) {
    const a = document.createElement('a');
    a.setAttribute('href', 'data:text/csv;charset=utf-8,' + encodeURIComponent(text));
    a.setAttribute('download', filename);
    document.body.appendChild(a); a.click(); document.body.removeChild(a);
  }
  function spiToCSV(){
    const headers = ['ID','Full Name','Email','Phone','Preferred Date','Urgency','Status','Created'];
    const rows = spiRows().filter(tr => tr.style.display !== 'none');
    const lines = [headers.join(',')];
    rows.forEach(tr => {
      const cells = Array.from(tr.children).slice(0,8).map(td => '"' + (td.innerText||'').replace(/"/g,'\\"') + '"');
      lines.push(cells.join(','));
    });
    return lines.join('\n');
  }
  if (spiExport) spiExport.addEventListener('click', () => download('spi_health_inspections.csv', spiToCSV()));

  // View modal helpers
  function viewField(label, value) {
    const safe = (value ?? '').toString();
    return `<div class="modal-field border rounded p-3">\n      <div class="text-gray-400 text-xs mb-1">${label}</div>\n      <div class="text-white break-words whitespace-pre-wrap">${safe.replace(/</g,'&lt;').replace(/>/g,'&gt;')}</div>\n    </div>`;
  }
  function openViewModal(row){
    const modal = document.getElementById('spi-view-modal');
    const details = document.getElementById('spi-view-details');
    if (!modal || !details || !row) return;
    details.innerHTML = `
      ${viewField('Request ID', '#' + (row.dataset.id || ''))}
      ${viewField('Service', 'Health Inspection')}
      ${viewField('Status', (row.dataset.status || '').toUpperCase())}
      ${viewField('Urgency', (row.dataset.urgency || '').toUpperCase())}
      ${viewField('Created At', row.dataset.created || '')}
      ${viewField('Full Name', row.dataset.name || '')}
      ${viewField('Email', row.dataset.email || '')}
      ${viewField('Phone', row.dataset.phone || '')}
      ${viewField('Preferred Date', row.dataset.preferred_date || '')}
      ${viewField('Address', row.dataset.address || '')}
      ${viewField('Service Details', row.dataset.details || '')}
    `;
    modal.classList.add('show');
    modal.classList.remove('hidden');
  }
  function closeViewModal(){
    const modal = document.getElementById('spi-view-modal');
    if (!modal) return;
    modal.classList.remove('show');
    modal.classList.add('hidden');
  }
  window.openViewModal = openViewModal; window.closeViewModal = closeViewModal;

  // CRUD modal handling
  const crudModal = document.getElementById('spi-crud-modal');
  const crudForm = document.getElementById('crud-form');
  const crudTitle = document.getElementById('crud-title');
  let crudCurrentRow = null;

  function statusBadgeClass(status){
    switch ((status || '').toLowerCase()){
      case 'completed': return 'bg-blue-600 text-white';
      case 'in_progress': return 'bg-green-600 text-white';
      case 'cancelled': return 'bg-red-600 text-white';
      default: return 'bg-yellow-600 text-white';
    }
  }
  function urgencyBadgeClass(urgency){
    switch ((urgency || '').toLowerCase()){
      case 'emergency': return 'bg-red-600 text-white';
      case 'high': return 'bg-orange-600 text-white';
      case 'low': return 'bg-gray-500 text-white';
      default: return 'bg-yellow-600 text-white';
    }
  }

  function fillFormFromRow(row){
    const f = crudForm;
    f.elements['id'].value = row.dataset.id || '';
    f.elements['full_name'].value = row.dataset.name || '';
    f.elements['email'].value = row.dataset.email || '';
    f.elements['phone'].value = row.dataset.phone || '';
    f.elements['address'].value = row.dataset.address || '';
    f.elements['service_details'].value = row.dataset.details || '';
    f.elements['preferred_date'].value = row.dataset.preferred_date || '';
    f.elements['urgency'].value = (row.dataset.urgency || 'medium').toLowerCase();
    f.elements['status'].value = (row.dataset.status || 'pending').toLowerCase();
  }

  function openCrudModal(mode, row){
    if (!crudModal) return;
    crudForm.reset();
    crudForm.elements['mode'].value = mode;
    crudCurrentRow = null;
    if (mode === 'edit' && row) {
      crudTitle.textContent = 'Edit Health Inspection';
      fillFormFromRow(row);
      crudCurrentRow = row;
    } else {
      crudTitle.textContent = 'Add Health Inspection';
      crudForm.elements['status'].value = 'pending';
      crudForm.elements['urgency'].value = 'medium';
    }
    crudModal.classList.add('show');
    crudModal.classList.remove('hidden');
  }
  function closeCrudModal(){ if (!crudModal) return; crudModal.classList.remove('show'); crudModal.classList.add('hidden'); }
  window.openCrudModal = openCrudModal; window.closeCrudModal = closeCrudModal;
  if (spiAdd) spiAdd.addEventListener('click', () => openCrudModal('create'));

  async function apiCall(payload){
    const res = await fetch('spi/api.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload) });
    const text = await res.text();
    if (!res.ok) throw new Error(text || ('HTTP '+res.status));
    return JSON.parse(text);
  }

  async function deleteRow(row){
    if (!row) return;
    const id = parseInt(row.dataset.id||'0',10);
    if (!id) return alert('Invalid ID');
    if (!confirm('Delete this request? This cannot be undone.')) return;
    try {
      const result = await apiCall({ action:'delete', id });
      if (result.success){ row.remove(); applySpiFilters(); }
      else alert('Delete failed.');
    } catch(e){ console.error(e); alert('Server error.'); }
  }
  window.deleteRow = deleteRow;

  crudForm?.addEventListener('submit', async (e)=>{
    e.preventDefault();
    const mode = crudForm.elements['mode'].value;
    const id = parseInt(crudForm.elements['id'].value||'0',10);
    const payload = {};
    if (mode === 'edit') {
      if (!id) return alert('Invalid ID');
      payload.action = 'update';
      payload.id = id;
      ['status','urgency','preferred_date','address','service_details','full_name','email','phone'].forEach(k=>{
        const v = crudForm.elements[k]?.value ?? null;
        if (v !== null) payload[k] = v;
      });
    } else {
      payload.action = 'create';
      payload.service_type = 'health-inspection';
      payload.user_email = crudForm.elements['user_email'].value;
      payload.full_name = crudForm.elements['full_name'].value;
      payload.email = crudForm.elements['email'].value;
      payload.phone = crudForm.elements['phone'].value;
      payload.address = crudForm.elements['address'].value;
      payload.service_details = crudForm.elements['service_details'].value;
      payload.preferred_date = crudForm.elements['preferred_date'].value;
      payload.urgency = crudForm.elements['urgency'].value;
    }
    try {
      const result = await apiCall(payload);
      if (!result.success) throw new Error('Operation failed');
      if (mode === 'edit') {
        if (crudCurrentRow){
          const row = crudCurrentRow;
          if (payload.preferred_date !== undefined) {
            row.dataset.preferred_date = payload.preferred_date;
            const cell = row.children[4];
            if (cell) cell.innerText = payload.preferred_date;
          }
          if (payload.urgency !== undefined) {
            row.dataset.urgency = payload.urgency;
            const cell = row.children[5];
            if (cell){
              const span = cell.querySelector('span');
              if (span){
                span.textContent = (payload.urgency || '').toUpperCase();
                span.className = 'px-2 py-1 rounded text-xs font-medium ' + urgencyBadgeClass(payload.urgency);
              }
            }
          }
          if (payload.status !== undefined) {
            row.dataset.status = payload.status;
            const cell = row.children[6];
            if (cell){
              const span = cell.querySelector('span');
              if (span){
                span.textContent = (payload.status || '').toUpperCase();
                span.className = 'px-2 py-1 rounded text-xs font-medium ' + statusBadgeClass(payload.status);
              }
            }
          }
          if (payload.full_name !== undefined) {
            row.dataset.name = payload.full_name;
            const cell = row.children[1];
            if (cell) cell.innerText = payload.full_name;
          }
          if (payload.email !== undefined) {
            row.dataset.email = payload.email;
            const cell = row.children[2];
            if (cell) cell.innerText = payload.email;
          }
          if (payload.phone !== undefined) {
            row.dataset.phone = payload.phone;
            const cell = row.children[3];
            if (cell) cell.innerText = payload.phone;
          }
          if (payload.address !== undefined) row.dataset.address = payload.address;
          if (payload.service_details !== undefined) row.dataset.details = payload.service_details;
        }
        crudCurrentRow = null;
        closeCrudModal();
        applySpiFilters();
        alert('Save Changes Successfully.');
      } else {
        alert('Saved successfully');
        location.reload();
      }
    } catch(err){ console.error(err); alert('Error saving data.'); }
  });

  applySpiFilters();
</script>
