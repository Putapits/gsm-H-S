<?php
// Doctor Vaccination Requests (CRUD)
$serviceType = 'vaccination';
$typeLabel = 'Vaccination';
try {
  $stmt = $db->prepare("SELECT * FROM service_requests WHERE service_type = ? AND deleted_at IS NULL ORDER BY created_at DESC");
  $stmt->execute([$serviceType]);
  $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
  error_log('Doctor vaccination fetch error: ' . $e->getMessage());
  $requests = [];
}
function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>

<div class="mb-6">
  <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
    <div>
      <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Vaccination Programs</h2>
      <p class="mt-1 text-gray-600 dark:text-gray-400">Manage vaccination-related requests.</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
      <button id="vac-clear" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-600 transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700">Clear Filters</button>
      <button id="vac-export" class="inline-flex items-center rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-600 transition hover:bg-emerald-100 focus:outline-none focus:ring-2 focus:ring-emerald-200 dark:border-emerald-500/40 dark:bg-emerald-500/20 dark:text-emerald-100 dark:hover:bg-emerald-500/30">Export CSV</button>
      <button id="vac-add" class="inline-flex items-center rounded-lg bg-primary px-3 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/40">Add New</button>
      <div class="text-sm text-gray-600 dark:text-gray-300">Total: <span class="font-semibold text-gray-900 dark:text-white"><?php echo count($requests); ?></span></div>
    </div>
  </div>
</div>

<section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-lg dark:border-slate-700 dark:bg-slate-900/70">
  <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-3">
    <div class="md:col-span-2">
      <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Search</label>
      <div class="relative">
        <input id="vac-search" type="text" placeholder="Search name, email, phone, details..." class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2 pr-10 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
        <svg class="absolute right-3 top-2.5 h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
      </div>
    </div>
    <div>
      <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</label>
      <select id="vac-filter-status" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
        <option value="">All</option>
        <option value="pending">Pending</option>
        <option value="in_progress">In Progress</option>
        <option value="completed">Completed</option>
        <option value="cancelled">Cancelled</option>
      </select>
    </div>
  </div>

  <div class="overflow-x-auto">
    <table class="min-w-full text-left text-sm text-gray-700 dark:text-gray-200" id="vac-table">
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
      <tbody id="vac-tbody">
        <?php if (empty($requests)): ?>
          <tr><td colspan="9" class="py-6 px-3 text-center text-gray-500 dark:text-gray-400">No vaccination requests found.</td></tr>
        <?php else: foreach ($requests as $r): ?>
          <tr class="vac-row border-b border-gray-100 transition-colors hover:bg-gray-50 dark:border-slate-800 dark:hover:bg-slate-800/60"
              data-id="<?php echo h($r['id']); ?>"
              data-name="<?php echo h($r['full_name']); ?>"
              data-email="<?php echo h($r['email']); ?>"
              data-phone="<?php echo h($r['phone']); ?>"
              data-preferred_date="<?php echo h(isset($r['preferred_date']) ? $r['preferred_date'] : ''); ?>"
              data-urgency="<?php echo h(isset($r['urgency']) ? $r['urgency'] : ''); ?>"
              data-status="<?php echo h($r['status']); ?>"
              data-created="<?php echo h($r['created_at']); ?>"
              data-address="<?php echo h($r['address']); ?>"
              data-details="<?php echo h($r['service_details']); ?>">
            <td class="py-3 px-3 font-medium text-gray-700 dark:text-gray-200">#<?php echo h($r['id']); ?></td>
            <td class="py-3 px-3 text-gray-600 dark:text-gray-300"><?php echo h($r['full_name']); ?></td>
            <td class="py-3 px-3 text-gray-600 dark:text-gray-300"><?php echo h($r['email']); ?></td>
            <td class="py-3 px-3 text-gray-600 dark:text-gray-300"><?php echo h($r['phone']); ?></td>
            <td class="py-3 px-3 text-gray-600 dark:text-gray-300"><?php echo h(isset($r['preferred_date']) ? $r['preferred_date'] : ''); ?></td>
            <td class="py-3 px-3">
              <?php $u = strtolower(isset($r['urgency']) ? $r['urgency'] : 'medium');
                $urgencyClasses = [
                  'emergency' => 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-100',
                  'high' => 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-100',
                  'low' => 'bg-slate-100 text-slate-700 dark:bg-slate-600/40 dark:text-slate-100',
                  'medium' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-200',
                ];
                $urgencyClass = $urgencyClasses[$u] ?? 'bg-gray-200 text-gray-700 dark:bg-slate-700 dark:text-gray-100';
              ?>
              <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold <?php echo $urgencyClass; ?>"><?php echo h(isset($r['urgency']) ? strtoupper($r['urgency']) : 'MEDIUM'); ?></span>
            </td>
            <td class="py-3 px-3">
              <?php $s = strtolower(isset($r['status']) ? $r['status'] : 'pending');
                $statusClasses = [
                  'completed' => 'bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-100',
                  'in_progress' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-100',
                  'cancelled' => 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-100',
                  'pending' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-200',
                ];
                $statusClass = $statusClasses[$s] ?? 'bg-gray-200 text-gray-700 dark:bg-slate-700 dark:text-gray-100';
              ?>
              <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold <?php echo $statusClass; ?>"><?php echo h(strtoupper(isset($r['status']) ? $r['status'] : 'PENDING')); ?></span>
            </td>
            <td class="py-3 px-3 text-gray-600 dark:text-gray-300"><?php echo h($r['created_at']); ?></td>
            <td class="py-3 px-3 text-right">
              <button class="mr-2 inline-flex items-center rounded-lg bg-primary px-3 py-1 text-xs font-semibold text-white shadow-sm transition hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/40" onclick="openViewModal(this.closest('tr'))">View</button>
              <button class="mr-2 inline-flex items-center rounded-lg bg-amber-500 px-3 py-1 text-xs font-semibold text-white shadow-sm transition hover:bg-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-400/60" onclick="openCrudModal('edit', this.closest('tr'))">Edit</button>
              <button class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-1 text-xs font-semibold text-gray-600 transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700" onclick="deleteRow(this.closest('tr'))">Delete</button>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
  <div id="vac-pagination" class="mt-6 flex items-center justify-end gap-2 text-sm text-gray-600 dark:text-gray-300"></div>
</section>

<!-- View Modal -->
<div id="vac-view-modal" class="fixed inset-0 hidden items-center justify-center z-50">
  <div class="absolute inset-0 bg-black/60" onclick="closeViewModal()"></div>
  <div class="relative max-h-[85vh] w-[92%] max-w-3xl overflow-y-auto rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl dark:border-slate-700 dark:bg-slate-900">
    <div class="mb-4 flex items-center justify-between">
      <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Vaccination Request Details</h3>
      <button class="rounded-full p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-slate-800" onclick="closeViewModal()">✕</button>
    </div>
    <div id="vac-view-details" class="grid grid-cols-1 gap-4 text-sm text-gray-700 md:grid-cols-2 dark:text-gray-200"></div>
  </div>
  <style>#vac-view-modal.show{display:flex;}</style>
</div>

<!-- CRUD Modal -->
<div id="vac-crud-modal" class="fixed inset-0 hidden items-center justify-center z-50">
  <div class="absolute inset-0 bg-black/60" onclick="closeCrudModal()"></div>
  <div class="relative max-h-[90vh] w-[92%] max-w-3xl overflow-y-auto rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl dark:border-slate-700 dark:bg-slate-900">
    <div class="mb-4 flex items-center justify-between">
      <h3 id="vac-crud-title" class="text-xl font-semibold text-gray-900 dark:text-white">Add Vaccination Request</h3>
      <button class="rounded-full p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-slate-800" onclick="closeCrudModal()">✕</button>
    </div>
    <form id="vac-crud-form" class="grid grid-cols-1 gap-4 text-sm md:grid-cols-2">
      <input type="hidden" name="mode" value="create" />
      <input type="hidden" name="id" />

      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">User Email (for create)</label>
        <input name="user_email" type="email" placeholder="citizen@example.com" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Full Name</label>
        <input name="full_name" type="text" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Email</label>
        <input name="email" type="email" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Phone</label>
        <input name="phone" type="text" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div class="md:col-span-2">
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Address</label>
        <input name="address" type="text" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div class="md:col-span-2">
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Service Details</label>
        <textarea name="service_details" rows="4" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white"></textarea>
      </div>

      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Preferred Date</label>
        <input name="preferred_date" type="date" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Urgency</label>
        <select name="urgency" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
          <option value="low">Low</option>
          <option value="medium">Medium</option>
          <option value="high">High</option>
          <option value="emergency">Emergency</option>
        </select>
      </div>
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</label>
        <select name="status" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
          <option value="pending">Pending</option>
          <option value="in_progress">In Progress</option>
          <option value="completed">Completed</option>
          <option value="cancelled">Cancelled</option>
        </select>
      </div>

      <div class="md:col-span-2 flex justify-end gap-2 pt-2">
        <button type="button" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-600 transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700" onclick="closeCrudModal()">Cancel</button>
        <button type="submit" class="inline-flex items-center rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/40">Save</button>
      </div>
    </form>
  </div>
  <style>#vac-crud-modal.show { display:flex; }</style>
</div>

<script>
  const vacSearch = document.getElementById('vac-search');
  const vacBody = document.getElementById('vac-tbody');
  const vacFilterStatus = document.getElementById('vac-filter-status');
  const vacTable = document.getElementById('vac-table');
  const vacExport = document.getElementById('vac-export');
  const vacPagination = document.getElementById('vac-pagination');
  const vacAdd = document.getElementById('vac-add');
  let vacSortKey = 'id'; let vacSortAsc = false; let vacPage = 1; const vacPageSize = 10;

  function vacRows(){ return Array.from(vacBody?.querySelectorAll('.vac-row') || []); }
  function applyVacFilters(){
    const q = (vacSearch?.value||'').toLowerCase(); const fs = (vacFilterStatus?.value||'').toLowerCase();
    let rows = vacRows();
    rows.forEach(tr=>{ const text = tr.innerText.toLowerCase(); const s=(tr.dataset.status||'').toLowerCase(); tr.dataset._match = (text.includes(q) && (!fs||s===fs))?'1':'0'; });
    rows = rows.filter(tr=>tr.dataset._match==='1');
    rows.sort((a,b)=>{ const ka=(a.dataset[vacSortKey]||'').toLowerCase(); const kb=(b.dataset[vacSortKey]||'').toLowerCase(); if(ka<kb)return vacSortAsc?-1:1; if(ka>kb)return vacSortAsc?1:-1; return 0; });
    const total = rows.length; const pages = Math.max(1, Math.ceil(total/vacPageSize)); if (vacPage>pages) vacPage=pages; const start=(vacPage-1)*vacPageSize; const visible=new Set(rows.slice(start,start+vacPageSize));
    vacRows().forEach(tr=>tr.style.display='none'); visible.forEach(tr=>tr.style.display='');
    renderVacPagination(pages);
  }
  function renderVacPagination(pages){
    if(!vacPagination)return;
    vacPagination.innerHTML='';
    const baseBtn = ()=>{
      const btn=document.createElement('button');
      btn.type='button';
      btn.className='px-3 py-1 rounded-lg border text-sm font-medium transition focus:outline-none focus:ring-2 focus:ring-primary/30 border-gray-300 bg-white text-gray-600 hover:bg-gray-100 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700';
      return btn;
    };
    const prev=baseBtn();
    prev.textContent='Prev';
    prev.disabled=vacPage<=1;
    if(prev.disabled){ prev.classList.add('opacity-60','cursor-not-allowed'); } else { prev.onclick=()=>{vacPage--;applyVacFilters();}; }
    vacPagination.appendChild(prev);
    for(let i=1;i<=pages;i++){
      const b=baseBtn();
      b.textContent=i;
      if(i===vacPage){
        b.className='px-3 py-1 rounded-lg bg-primary text-white text-sm font-semibold shadow-sm focus:outline-none focus:ring-2 focus:ring-primary/40';
      } else {
        b.onclick=()=>{vacPage=i;applyVacFilters();};
      }
      vacPagination.appendChild(b);
    }
    const next=baseBtn();
    next.textContent='Next';
    next.disabled=vacPage>=pages;
    if(next.disabled){ next.classList.add('opacity-60','cursor-not-allowed'); } else { next.onclick=()=>{vacPage++;applyVacFilters();}; }
    vacPagination.appendChild(next);
  }
  [vacSearch, vacFilterStatus].forEach(el=>{ if(el) el.addEventListener('input', applyVacFilters); if(el) el.addEventListener('change', applyVacFilters); });
  const vacClearBtn = document.getElementById('vac-clear');
  if (vacClearBtn){ vacClearBtn.addEventListener('click', function(){ if(vacSearch) vacSearch.value=''; if(vacFilterStatus) vacFilterStatus.value=''; vacPage=1; applyVacFilters(); }); }
  if (vacTable){ vacTable.querySelectorAll('th.sortable').forEach(th=>{ th.addEventListener('click',()=>{ const key=th.getAttribute('data-key'); if(vacSortKey===key){vacSortAsc=!vacSortAsc;} else {vacSortKey=key; vacSortAsc=true;} applyVacFilters(); }); }); }
  function download(filename, text){ const a=document.createElement('a'); a.href='data:text/csv;charset=utf-8,'+encodeURIComponent(text); a.download=filename; document.body.appendChild(a); a.click(); document.body.removeChild(a); }
  function vacToCSV(){ const headers=['ID','Full Name','Email','Phone','Preferred Date','Urgency','Status','Created']; const rows=vacRows().filter(tr=>tr.style.display!=='none'); const lines=[headers.join(',')]; rows.forEach(tr=>{ const cells=Array.from(tr.children).slice(0,8).map(td=>'"'+(td.innerText||'').replace(/"/g,'\\"')+'"'); lines.push(cells.join(',')); }); return lines.join('\n'); }
  if (vacExport) vacExport.addEventListener('click', ()=>download('vaccination_requests.csv', vacToCSV()));

  function viewField(label, value){ const safe=(value??'').toString(); return `<div class="rounded-lg border border-gray-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800/60">
  <div class="mb-1 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">${label}</div>
  <div class="break-words text-sm text-gray-900 whitespace-pre-wrap dark:text-gray-100">${safe.replace(/</g,'&lt;').replace(/>/g,'&gt;')}</div>
</div>`; }
  function openViewModal(row){ const modal=document.getElementById('vac-view-modal'); const details=document.getElementById('vac-view-details'); if(!modal||!details||!row)return; details.innerHTML=`
    ${viewField('Request ID', '#'+(row.dataset.id||''))}
    ${viewField('Status', (row.dataset.status||'').toUpperCase())}
    ${viewField('Urgency', (row.dataset.urgency||'').toUpperCase())}
    ${viewField('Created At', row.dataset.created||'')}
    ${viewField('Full Name', row.dataset.name||'')}
    ${viewField('Email', row.dataset.email||'')}
    ${viewField('Phone', row.dataset.phone||'')}
    ${viewField('Preferred Date', row.dataset.preferred_date||'')}
    ${viewField('Address', row.dataset.address||'')}
    ${viewField('Service Details', row.dataset.details||'')}
  `; modal.classList.add('show'); modal.classList.remove('hidden'); }
  function closeViewModal(){ const modal=document.getElementById('vac-view-modal'); if(!modal)return; modal.classList.remove('show'); modal.classList.add('hidden'); }
  window.openViewModal=openViewModal; window.closeViewModal=closeViewModal;

  const vacCrudModal=document.getElementById('vac-crud-modal'); const vacCrudForm=document.getElementById('vac-crud-form'); const vacCrudTitle=document.getElementById('vac-crud-title');
  function fillFormFromRow(row){ const f=vacCrudForm; f.elements['id'].value=row.dataset.id||''; f.elements['full_name'].value=row.dataset.name||''; f.elements['email'].value=row.dataset.email||''; f.elements['phone'].value=row.dataset.phone||''; f.elements['address'].value=row.dataset.address||''; f.elements['service_details'].value=row.dataset.details||''; f.elements['preferred_date'].value=row.dataset.preferred_date||''; f.elements['urgency'].value=(row.dataset.urgency||'medium').toLowerCase(); f.elements['status'].value=(row.dataset.status||'pending').toLowerCase(); }
  function openCrudModal(mode,row){ if(!vacCrudModal)return; vacCrudForm.reset(); vacCrudForm.elements['mode'].value=mode; if(mode==='edit'&&row){ vacCrudTitle.textContent='Edit Vaccination Request'; fillFormFromRow(row);} else { vacCrudTitle.textContent='Add Vaccination Request'; vacCrudForm.elements['status'].value='pending'; vacCrudForm.elements['urgency'].value='medium'; } vacCrudModal.classList.add('show'); vacCrudModal.classList.remove('hidden'); }
  function closeCrudModal(){ if(!vacCrudModal)return; vacCrudModal.classList.remove('show'); vacCrudModal.classList.add('hidden'); }
  window.openCrudModal=openCrudModal; window.closeCrudModal=closeCrudModal; if(vacAdd) vacAdd.addEventListener('click',()=>openCrudModal('create'));

  async function apiCall(payload){ const res=await fetch('int/api.php',{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json'},credentials:'same-origin',body:JSON.stringify(payload)}); const text=await res.text(); let json=null; try{ json=JSON.parse(text);}catch{} if(!res.ok){ throw new Error((json&&json.message)||text||('HTTP '+res.status)); } return json||{}; }
  async function deleteRow(row){ if(!row) return; const id=parseInt(row.dataset.id||'0',10); if(!id) return alert('Invalid ID'); if(!confirm('Delete this request?')) return; try{ const result=await apiCall({action:'delete', id}); if(result.success){ row.remove(); applyVacFilters(); } else alert('Delete failed.'); }catch(e){ console.error(e); alert('Server error.'); } }
  window.deleteRow=deleteRow;

  vacCrudForm?.addEventListener('submit', async (e)=>{ e.preventDefault(); const mode=vacCrudForm.elements['mode'].value; const id=parseInt(vacCrudForm.elements['id'].value||'0',10); const payload={}; if(mode==='edit'){ if(!id) return alert('Invalid ID'); payload.action='update'; payload.id=id; ['status','urgency','preferred_date','address','service_details','full_name','email','phone'].forEach(k=>{ const v=vacCrudForm.elements[k]?.value ?? null; if(v!==null) payload[k]=v; }); } else { payload.action='create'; payload.service_type='<?php echo $serviceType; ?>'; payload.user_email=vacCrudForm.elements['user_email'].value; payload.full_name=vacCrudForm.elements['full_name'].value; payload.email=vacCrudForm.elements['email'].value; payload.phone=vacCrudForm.elements['phone'].value; payload.address=vacCrudForm.elements['address'].value; payload.service_details=vacCrudForm.elements['service_details'].value; payload.preferred_date=vacCrudForm.elements['preferred_date'].value; payload.urgency=vacCrudForm.elements['urgency'].value; } try{ const result=await apiCall(payload); if(!result.success) throw new Error(JSON.stringify(result)); alert('Saved successfully'); location.reload(); } catch(err){ console.error(err); let msg='Error saving data.'; try{ const j=JSON.parse(err.message); if(j&&j.message) msg=j.message; }catch{} alert(msg); } });

  applyVacFilters();
</script>
