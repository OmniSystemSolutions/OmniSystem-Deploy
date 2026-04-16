{{--
  Shared read-only canvas viewer for table layouts.
  Include inside @section('scripts') wherever a layout needs to be rendered
  without editing capabilities.

  Exports (global):
    renderLayoutCanvas(canvasEl, data, ordersMap, onOccupiedClick)
      canvasEl        – the DOM element to render into
      data            – layout data: { floors: { "1": [...items] } }
      ordersMap       – plain object keyed by tableNo: { id, status }
      onOccupiedClick – optional callback(order) when an occupied table is clicked
--}}

<style id="cv-styles">
/* ===== Shared Canvas Viewer Styles ===== */
.cv-canvas {
   position: relative;
   min-height: 500px;
   min-width: 800px;
   background: repeating-linear-gradient(90deg, #a86b32, #a86b32 20px, #9c5f2c 20px, #9c5f2c 40px);
}
/* Viewer overrides: no cursor-move, no resize handle */
.cv-canvas .placed-table {
   position: absolute;
   display: flex;
   align-items: center;
   justify-content: center;
   font-size: 11px;
   font-weight: bold;
   color: #fff;
   user-select: none;
   min-width: 50px;
   min-height: 50px;
   box-shadow: 0 6px 12px rgba(0,0,0,0.15);
   transition: box-shadow 0.2s ease;
   cursor: default;
   overflow: hidden;
}
.cv-canvas .table-small  { width:60px;  height:60px;  border-radius:50%;  background:radial-gradient(circle,#66bb6a,#2e7d32); }
.cv-canvas .table-medium { width:80px;  height:80px;  border-radius:10px; background:linear-gradient(145deg,#42a5f5,#1565c0); }
.cv-canvas .table-large  { width:120px; height:70px;  border-radius:10px; background:linear-gradient(145deg,#ffb74d,#ef6c00); }
.cv-canvas .merged-group { border:2px solid #0077cc; background:linear-gradient(145deg,#e3f2fd,#bbdefb); color:#333; box-shadow:0 8px 18px rgba(0,0,0,0.2); border-radius:12px; }
.cv-canvas .occupied     { background:#f44336 !important; color:#fff !important; cursor:pointer !important; box-shadow:0 0 0 3px rgba(244,67,54,0.35); }
.cv-canvas .occupied:hover { box-shadow:0 0 0 4px rgba(244,67,54,0.55); }
.cv-legend { display:flex; gap:14px; font-size:12px; align-items:center; }
.cv-dot    { width:12px; height:12px; border-radius:50%; display:inline-block; margin-right:4px; vertical-align:middle; }
</style>

<script>
function cvGetSizeClass(size) {
   if (size === 'small') return 'table-small';
   if (size === 'large') return 'table-large';
   return 'table-medium';
}

/**
 * Render a saved layout onto a canvas element (read-only).
 *
 * @param {HTMLElement} canvasEl        Container div (add class "cv-canvas" for styles)
 * @param {object}      data            Layout data: { floors: { "1": [...items] } }
 * @param {object}      ordersMap       Keyed by tableNo string: { id, status }
 * @param {Function}    onOccupiedClick Called with the order object on click (optional)
 */
function renderLayoutCanvas(canvasEl, data, ordersMap, onOccupiedClick) {
   canvasEl.innerHTML = '';
   canvasEl.classList.add('cv-canvas');

   const floorsData = (data && data.floors) ? data.floors : {};
   Object.keys(floorsData).sort().forEach(function(floorId) {
      var items = floorsData[floorId];
      if (!Array.isArray(items)) return;
      items.forEach(function(item) {
         canvasEl.appendChild(_cvMakeEl(item, ordersMap || {}, onOccupiedClick));
      });
   });
}

/**
 * Render a single floor's items onto the canvas (clears previous content).
 *
 * @param {HTMLElement} canvasEl        Container div
 * @param {Array}       items           Array of table/merged items for one floor
 * @param {object}      ordersMap       Keyed by tableNo string: { id, status }
 * @param {Function}    onOccupiedClick Called with the order object on click (optional)
 */
function renderFloorItems(canvasEl, items, ordersMap, onOccupiedClick, onAvailableClick) {
   canvasEl.innerHTML = '';
   canvasEl.classList.add('cv-canvas');
   if (!Array.isArray(items)) return;
   items.forEach(function(item) {
      canvasEl.appendChild(_cvMakeEl(item, ordersMap || {}, onOccupiedClick, onAvailableClick));
   });
}

function _cvMakeEl(s, ordersMap, onOccupiedClick, onAvailableClick) {
   var el       = document.createElement('div');
   var isMerged = s.type === 'merged';
   var order    = ordersMap[String(s.tableNo)];

   var cls = 'placed-table';
   cls += isMerged ? ' merged-group' : (' ' + cvGetSizeClass(s.size));
   if (order) cls += ' occupied';
   el.className = cls;

   el.style.left = (s.x || 0) + 'px';
   el.style.top  = (s.y || 0) + 'px';
   if (s.w) el.style.width  = s.w + 'px';
   if (s.h) el.style.height = s.h + 'px';
   if (isMerged && !s.w) el.style.width  = '120px';
   if (isMerged && !s.h) el.style.height = '80px';
   if (s.rotation) el.style.transform = 'rotate(' + s.rotation + 'deg)';

   if (order) {
      el.title       = 'Order #' + order.id + ' \u00b7 ' + order.status;
      el.style.cursor = 'pointer';
      if (typeof onOccupiedClick === 'function') {
         el.addEventListener('click', function() { onOccupiedClick(order); });
      }
   } else {
      el.title       = 'Available \u2014 click to add order';
      el.style.cursor = typeof onAvailableClick === 'function' ? 'pointer' : 'default';
      if (typeof onAvailableClick === 'function') {
         el.addEventListener('click', function() { onAvailableClick(s.tableNo); });
      }
   }

   el.innerHTML = '<span style="text-align:center;padding:2px;">'
      + (s.label || ('Table ' + s.tableNo))
      + '</span>';
   return el;
}
</script>
