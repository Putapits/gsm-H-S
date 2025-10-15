<?php
require_once '../include/database.php';
startSecureSession();
requireRole('inspector');
include 'inspectorheader.php';
include 'inspectorsidebar.php';
?>

<main id="main-content" class="transition-all duration-300 ease-in-out ml-64 pt-16">
  <div id="content-area" class="min-h-screen bg-slate-100 p-6 dark:bg-dark-bg">
    <?php
      $page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
      if ($page === 'spi') {
        $allowedViews = ['overview','sanitation','permit'];
        $view = isset($_GET['view']) && in_array($_GET['view'], $allowedViews) ? $_GET['view'] : 'overview';
        include 'spi/' . $view . '.php';
      } elseif ($page === 'wss') {
        $allowedViews = ['overview','inspection','maintenance','installation'];
        $view = isset($_GET['view']) && in_array($_GET['view'], $allowedViews) ? $_GET['view'] : 'overview';
        include 'wss/' . $view . '.php';
      } elseif ($page === 'account') {
        $allowedViews = ['settings'];
        $view = isset($_GET['view']) && in_array($_GET['view'], $allowedViews) ? $_GET['view'] : 'settings';
        include 'account/' . $view . '.php';
      } elseif ($page === 'dashboard') {
        $spiTypes = ['business-permit','health-inspection'];
        $wssTypes = ['system-inspection','maintenance-service','installation-upgrade'];
        function i_countPendingByTypes(PDO $db, array $types){ if(!$types)return[]; try{ $ph=implode(',',array_fill(0,count($types),'?')); $sql="SELECT service_type, COUNT(*) c FROM service_requests WHERE deleted_at IS NULL AND status='pending' AND service_type IN ($ph) GROUP BY service_type"; $st=$db->prepare($sql); $st->execute(array_values($types)); $out=[]; foreach($st->fetchAll(PDO::FETCH_ASSOC) as $r){ $out[$r['service_type']]=(int)$r['c']; } return $out; } catch(Throwable $e){ return []; } }
        $spiBy = i_countPendingByTypes($db, $spiTypes);
        $wssBy = i_countPendingByTypes($db, $wssTypes);
        $pendingModules = [
          ['title'=>'Sanitation Permit & Inspection (SPI)','services'=>[
            ['label'=>'Business Permit','count'=>(int)($spiBy['business-permit']??0)],
            ['label'=>'Health Inspection','count'=>(int)($spiBy['health-inspection']??0)],
          ]],
          ['title'=>'Wastewater & Septic Services (WSS)','services'=>[
            ['label'=>'System Inspection','count'=>(int)($wssBy['system-inspection']??0)],
            ['label'=>'Maintenance Service','count'=>(int)($wssBy['maintenance-service']??0)],
            ['label'=>'Installation & Upgrade','count'=>(int)($wssBy['installation-upgrade']??0)],
          ]],
        ];
        $totalPending=0;
        foreach($pendingModules as $mod){
          foreach($mod['services'] as $svc){
            $totalPending += (int)($svc['count'] ?? 0);
          }
        }
        function i_timeAgo($ts){ $t=is_numeric($ts)?(int)$ts:strtotime((string)$ts); if(!$t)return 'just now'; $d=max(1,time()-$t); if($d<60)return $d.' seconds ago'; if($d<3600)return floor($d/60).' minutes ago'; if($d<86400)return floor($d/3600).' hours ago'; return floor($d/86400).' days ago'; }
        $recent = [];
        if (!empty($_SESSION['user_id'])) { try { $recent = $database->getAuditLogsPaginated(['user_id'=>$_SESSION['user_id']], 5, 0); } catch (Throwable $e) { $recent = []; } }
        ?>
        <div class="mb-10 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
          <div>
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Inspector Dashboard</h2>
            <p class="mt-1 text-gray-600 dark:text-gray-400">Monitor SPI and WSS requests and jump into the items that need your review.</p>
          </div>
          <div class="inline-flex items-center gap-2 rounded-full border border-primary/30 bg-primary/10 px-4 py-2 text-sm font-semibold text-primary dark:border-primary/40 dark:bg-primary/15 dark:text-primary-200">
            <span class="hidden sm:inline">Total pending workloads:</span>
            <span><?php echo number_format($totalPending); ?></span>
          </div>
        </div>
        <div class="mb-8 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
          <?php foreach ($pendingModules as $mod): ?>
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-lg dark:border-slate-700 dark:bg-slate-900/70">
              <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-4"><?php echo htmlspecialchars($mod['title'], ENT_QUOTES, 'UTF-8'); ?></p>
              <div class="space-y-3">
                <?php foreach ($mod['services'] as $svc): ?>
                  <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200"><?php echo htmlspecialchars($svc['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo number_format((int)$svc['count']); ?></span>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
          <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-lg dark:border-slate-700 dark:bg-slate-900/70">
            <h3 class="mb-6 text-lg font-semibold text-gray-900 dark:text-white">Quick Actions</h3>
            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
              <button onclick="location.href='inspector.php?page=spi&view=permit'" class="w-full text-left">
                <span class="inline-flex w-full items-center justify-between gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100 focus:outline-none focus:ring-2 focus:ring-emerald-200 dark:border-emerald-500/40 dark:bg-emerald-500/20 dark:text-emerald-100 dark:hover:bg-emerald-500/30">
                  <span>SPI Permits</span>
                  <svg class="h-5 w-5 text-emerald-500 dark:text-emerald-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
              </button>
              <button onclick="location.href='inspector.php?page=spi&view=sanitation'" class="w-full text-left">
                <span class="inline-flex w-full items-center justify-between gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100 focus:outline-none focus:ring-2 focus:ring-emerald-200 dark:border-emerald-500/40 dark:bg-emerald-500/20 dark:text-emerald-100 dark:hover:bg-emerald-500/30">
                  <span>SPI Inspections</span>
                  <svg class="h-5 w-5 text-emerald-500 dark:text-emerald-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
              </button>
              <button onclick="location.href='inspector.php?page=wss&view=inspection'" class="w-full text-left">
                <span class="inline-flex w-full items-center justify-between gap-3 rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm font-semibold text-sky-700 transition hover:bg-sky-100 focus:outline-none focus:ring-2 focus:ring-sky-200 dark:border-sky-500/40 dark:bg-sky-500/20 dark:text-sky-100 dark:hover:bg-sky-500/30">
                  <span>WSS Inspection</span>
                  <svg class="h-5 w-5 text-sky-500 dark:text-sky-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </span>
              </button>
              <button onclick="location.href='inspector.php?page=wss&view=maintenance'" class="w-full text-left">
                <span class="inline-flex w-full items-center justify-between gap-3 rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm font-semibold text-sky-700 transition hover:bg-sky-100 focus:outline-none focus:ring-2 focus:ring-sky-200 dark:border-sky-500/40 dark:bg-sky-500/20 dark:text-sky-100 dark:hover:bg-sky-500/30">
                  <span>WSS Maintenance</span>
                  <svg class="h-5 w-5 text-sky-500 dark:text-sky-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
              </button>
              <button onclick="location.href='inspector.php?page=wss&view=installation'" class="w-full text-left md:col-span-2">
                <span class="inline-flex w-full items-center justify-between gap-3 rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm font-semibold text-sky-700 transition hover:bg-sky-100 focus:outline-none focus:ring-2 focus:ring-sky-200 dark:border-sky-500/40 dark:bg-sky-500/20 dark:text-sky-100 dark:hover:bg-sky-500/30">
                  <span>WSS Installation</span>
                  <svg class="h-5 w-5 text-sky-500 dark:text-sky-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </span>
              </button>
            </div>
          </div>
          <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-lg dark:border-slate-700 dark:bg-slate-900/70">
            <h3 class="mb-6 text-lg font-semibold text-gray-900 dark:text-white">Recent Activity</h3>
            <div class="space-y-4">
              <?php if (empty($recent)): ?>
                <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 px-4 py-6 text-center text-sm text-gray-500 dark:border-slate-700 dark:bg-slate-800/40 dark:text-gray-300">No recent activity.</div>
              <?php else: foreach ($recent as $ra): ?>
                <?php $fn = trim(((string)($ra['first_name'] ?? '')) . ' ' . ((string)($ra['last_name'] ?? ''))); $act = strtolower((string)($ra['action'] ?? '')); $dot = ($act==='login' ? 'bg-emerald-500' : ($act==='logout' ? 'bg-slate-500' : ($act==='profile_update' ? 'bg-amber-500' : ($act==='password_change' ? 'bg-sky-500' : 'bg-indigo-500')))); ?>
                <div class="flex items-start gap-3 rounded-xl border border-gray-200 bg-white/60 p-4 dark:border-slate-800 dark:bg-slate-800/60">
                  <div class="mt-1.5 h-2.5 w-2.5 rounded-full <?php echo $dot; ?>"></div>
                  <div>
                    <p class="text-sm font-medium text-gray-800 dark:text-gray-100"><?php echo htmlspecialchars((string)($ra['action'] ?? ''), ENT_QUOTES, 'UTF-8'); ?><?php echo $fn ? ' · ' . htmlspecialchars($fn, ENT_QUOTES, 'UTF-8') : ''; ?></p>
                    <p class="text-xs text-gray-500 dark:text-gray-400"><?php echo i_timeAgo($ra['created_at'] ?? ''); ?></p>
                  </div>
                </div>
              <?php endforeach; endif; ?>
            </div>
          </div>
        </div>
        <?php
      } else {
        echo '<div class="text-white">Invalid page.</div>';
      }
    ?>
  </div>
</main>

<script>
  function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('main-content');
    const toggleButton = document.getElementById('sidebar-toggle');
    if (!sidebar || !mainContent || !toggleButton) return;
    sidebar.classList.toggle('sidebar-collapsed');
    if (sidebar.classList.contains('sidebar-collapsed')) {
      mainContent.classList.remove('ml-64');
      mainContent.classList.add('ml-16');
      toggleButton.classList.remove('ml-64');
      toggleButton.classList.add('ml-16');
    } else {
      mainContent.classList.remove('ml-16');
      mainContent.classList.add('ml-64');
      toggleButton.classList.remove('ml-16');
      toggleButton.classList.add('ml-64');
    }
  }
  const sbt = document.getElementById('sidebar-toggle');
  if (sbt) sbt.addEventListener('click', toggleSidebar);
  function toggleDropdown(id) {
    const dd = document.getElementById(id);
    const arrow = document.getElementById(id.replace('-dropdown','-arrow'));
    if (dd) dd.classList.toggle('hidden');
    if (arrow) arrow.classList.toggle('rotate-180');
  }
  // Clean leftover nodes if any (parity with admin)
  document.addEventListener('DOMContentLoaded', function(){
    const sidebar = document.getElementById('sidebar');
    const main = document.getElementById('main-content');
    if (sidebar && main) {
      let node = main.previousElementSibling;
      while (node && node !== sidebar) {
        const prev = node.previousElementSibling;
        node.parentNode.removeChild(node);
        node = prev;
      }
    }
  });
</script>
