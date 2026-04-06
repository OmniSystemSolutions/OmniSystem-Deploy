@extends('layouts.app')
@section('content')
<style>

    /* Sidebar */
    .sidebar{width:var(--sidebar); border-right:1px solid #e6e6e6; padding:12px; background:#fafafa}
    .sidebar h3{margin:6px 0 12px;font-size:14px}
    .table-list{display:flex;flex-direction:column;gap:8px}
    .table-item{display:flex;align-items:center;justify-content:space-between;padding:8px;border-radius:6px;background:white;border:1px solid #ddd;cursor:grab}
    .controls{margin-top:12px;display:flex;gap:8px}
    button{cursor:pointer;padding:8px 10px;border-radius:6px;border:1px solid #ccc;background:white}
    button.primary{background:var(--accent);color:#fff;border-color:transparent}
    /* Main area */
    .main{flex:1;display:flex;flex-direction:column}
    .floors{display:flex;gap:8px;padding:12px;border-bottom:1px solid #eee;background:#fff}
    .floor-btn{padding:6px 10px;border-radius:6px;border:1px solid #ddd;background:#f8f8f8}
    .floor-btn.active{background:var(--accent);color:#fff;border-color:transparent}
    .canvas-wrap{flex:1;position:relative;overflow:auto;background:linear-gradient(90deg,#fbfbfb,#fff)}
    .canvas{
  position:relative;
  margin:20px;
  min-height:600px;
  border:2px dashed #eee;
  background: repeating-linear-gradient(
    90deg,
    #a86b32,
    #a86b32 20px,
    #9c5f2c 20px,
    #9c5f2c 40px
  );
}
  /* BASE */
.placed-table{
  position:absolute;
  display:flex;
  align-items:center;
  justify-content:center;
  font-size:11px;
  font-weight:bold;
  color:#fff;
  cursor:move;
  user-select:none;
  resize: both;
  overflow: visible;
  min-width:50px;
  min-height:50px;

  box-shadow:0 6px 12px rgba(0,0,0,0.15);
  transition:all 0.2s ease;
}

.table-small{
  width:60px;
  height:60px;
  border-radius:50%;
  background:radial-gradient(circle,#66bb6a,#2e7d32);
}

.table-medium{
  width:80px;
  height:80px;
  border-radius:10px;
  background:linear-gradient(145deg,#42a5f5,#1565c0);
}

.table-large{
  width:120px;
  height:70px;
  border-radius:10px;
  background:linear-gradient(145deg,#ffb74d,#ef6c00);
}

/* SELECT */
.placed-table.selected{
  outline:3px solid rgba(255,255,255,0.6);
}
    .merged{background:linear-gradient(135deg,#fff,#f0fbff);border-style:solid;border-color:#0077cc}
    /* topbar actions */
    .top-actions{display:flex;gap:8px;padding:10px;border-bottom:1px solid #eee;background:#fff}
    .help{margin-left:auto;color:#666;font-size:13px}
    .badge{font-size:12px;padding:3px 6px;border-radius:6px;background:#eee}
    /* hint */
    .hint{font-size:13px;color:#666;margin-top:8px}

.merged-group{
  border:2px solid #0077cc;
  background:linear-gradient(145deg,#e3f2fd,#bbdefb);
  box-shadow:0 8px 18px rgba(0,0,0,0.2);
  border-radius:12px;
}

    .merged-group.selected {
      outline:3px solid rgba(0,119,204,0.3);
    }

    .available { background:#4caf50 !important; color:#fff; }
.occupied  { background:#f44336 !important; color:#fff; }
.reserved  { background:#e0e0e0 !important; color:#333; }

.chair{
  width:14px;
  height:10px; /* 👈 not circle anymore */
  background:linear-gradient(145deg,#666,#2b2b2b);
  border-radius:4px;
  position:absolute;
  box-shadow:0 2px 4px rgba(0,0,0,0.3);
  border:1px solid #222;
  transform-origin:center;
  transition:transform 0.2s ease;
}

/* hover effect */
.chair:hover{
  transform:scale(1.2);
  background:linear-gradient(145deg,#888,#444);
}

.status-dot{
  position:absolute;
  top:4px;
  right:4px;
  width:8px;
  height:8px;
  border-radius:50%;
}

.resize-handle{
  width:12px;
  height:12px;
  background:#fff;
  border:2px solid #333;
  position:absolute;
  right:-6px;
  bottom:-6px;
  cursor:nwse-resize;
  border-radius:3px;
}
  </style>
<div class="main-content" id="app">
  <div>
    <div class="breadcrumb">
      <h1 class="mr-3">Table Layouts</h1>
      <ul>
        <li><a href="/table-layouts">Settings</a></li>
      </ul>
    </div>
    <div class="separator-breadcrumb border-top"></div>
  </div>
  <div class="sidebar">
    <h3>Tables (drag → canvas)</h3>
    <div class="table-list" id="tableList">
  <!-- Fixed sizes -->
  <div class="table-item" draggable="true" data-size="small" data-chairs="2">
    Small Table <span class="badge">S (2)</span>
  </div>

  <div class="table-item" draggable="true" data-size="medium" data-chairs="4">
    Medium Table <span class="badge">M (4)</span>
  </div>

  <div class="table-item" draggable="true" data-size="large" data-chairs="6">
    Large Table <span class="badge">L (6)</span>
  </div>

  <!-- Custom -->
  <div style="display:flex; gap:6px; align-items:center; margin-top:8px;">
    <input 
      id="customChairs" 
      type="number" 
      min="1" 
      placeholder="Chairs" 
      style="flex:1;padding:6px;border:1px solid #ccc;border-radius:6px"
    />
    <button id="addCustomTable" style="padding:6px 10px;">Add</button>
  </div>
</div>
    <div style="margin-top:8px;display:flex;gap:8px">
      <button id="addTableBtn" class="primary">Add</button>
      <button id="addFloorBtn">Add Floor</button>
    </div>

    <div class="hint">Tip: drag a table from the left, drop anywhere on the canvas. Shift+click multiple tables, then press "Merge".</div>
  </div>

  <div class="main">
    <div class="top-actions">
      <button id="mergeBtn">Merge Selected</button>
      <button id="unmergeBtn">Unmerge</button>
      <button id="clearBtn">Clear Floor</button>
      <div class="help">Selected: <span id="selectedCount">0</span></div>
    </div>

    <div class="floors" id="floors"></div>

    <div class="canvas-wrap">
      <div class="canvas" id="canvas" data-floor="1"></div>
    </div>
  </div>

</div>
@endsection
@section('scripts')
<script>
  let tableNumberCounter = 1;
  function getSizeClass(size){
    if(size === 'small') return 'table-small';
    if(size === 'large') return 'table-large';
    return 'table-medium';
  }
  document.getElementById('addCustomTable').addEventListener('click', () => {
  const chairs = document.getElementById('customChairs').value;

  if (!chairs || chairs <= 0) {
    alert('Enter valid number of chairs');
    return;
  }

  const node = document.createElement('div');
  node.className = 'table-item';
  node.draggable = true;

  node.dataset.size = 'custom';
  node.dataset.chairs = chairs;
  node.dataset.label = 'Custom (' + chairs + ')';

  node.innerHTML = `Custom Table <span class="badge">(${chairs})</span>`;

  document.getElementById('tableList').appendChild(node);

  document.getElementById('customChairs').value = '';
});
// Minimal, dependency-free layout manager
    (function(){
      const tableList = document.getElementById('tableList');
      const canvas = document.getElementById('canvas');
      const floorsEl = document.getElementById('floors');
      const selectedCount = document.getElementById('selectedCount');
      const mergeBtn = document.getElementById('mergeBtn');
      const unmergeBtn = document.getElementById('unmergeBtn');
      const clearBtn = document.getElementById('clearBtn');
      const addTableBtn = document.getElementById('addTableBtn');
      const newTableName = document.getElementById('newTableName');
      const newTableSize = document.getElementById('newTableSize');
      const addFloorBtn = document.getElementById('addFloorBtn');

      let floors = {1:{id:1,name:'Floor 1'}};
      let activeFloor = 1;
      let placedCounter = 0;
      let selected = new Set();

      // render floors
      function renderFloors(){
        floorsEl.innerHTML='';
        for(const fId of Object.keys(floors).sort((a,b)=>a-b)){
          const btn = document.createElement('button');
          btn.className='floor-btn'+(Number(fId)===activeFloor?' active':'');
          btn.textContent = floors[fId].name;
          btn.onclick = ()=>{switchFloor(Number(fId))};
          floorsEl.appendChild(btn);
        }
      }

      function switchFloor(id){
        activeFloor = id;
        canvas.dataset.floor = id;
        // clear canvas and draw elements that belong to this floor (we store in element.dataset.floor)
        Array.from(canvas.querySelectorAll('.placed-table, .merged-group')).forEach(el=>el.remove());
        // load from memory if we kept one (for brevity we keep in-memory elements under document._layout)
        const saved = (document._layout || []).filter(x=>Number(x.floor)===id);
        for(const s of saved) createPlacedFromSaved(s);
        renderFloors();
      }

      function createPlacedFromSaved(s){
        if(s.type==='merged'){
          const group = makeMergedElement(s);
          canvas.appendChild(group);
        } else {
          const el = makePlacedElement(s);
          canvas.appendChild(el);
        }
      }

      // drag from sidebar
      tableList.addEventListener('dragstart', e=>{
        const target = e.target.closest('.table-item');
        if(!target) return;
        e.dataTransfer.setData('text/plain', JSON.stringify({
          label: target.dataset.label || target.textContent.trim(),
          size: target.dataset.size || 'medium',
          chairs: Number(target.dataset.chairs || 0)
        }));
      });

      canvas.addEventListener('dragover', e=>{ e.preventDefault(); });
      canvas.addEventListener('drop', e=>{
  e.preventDefault();

  const payload = e.dataTransfer.getData('text/plain');

  if(!payload){
    console.error('No drag data');
    return;
  }

  let obj;
  try{
    obj = JSON.parse(payload);
  }catch(err){
    console.error('Invalid JSON:', payload);
    return;
  }

  const rect = canvas.getBoundingClientRect();

  const x = e.clientX - rect.left + canvas.scrollLeft;
  const y = e.clientY - rect.top + canvas.scrollTop;

  console.log('DROP:', obj); // 🔍 DEBUG

  createPlaced({
    label: obj.label,
    size: obj.size,
    chairs: obj.chairs || 0,
    x,
    y,
    floor: activeFloor
  });
});

      // create placed table
      function createPlaced({label=null,size='medium',x=100,y=100,floor=1,id=null,chairs=0}){
        const tableNo = tableNumberCounter++;

        const s = {
          type:'table',
          id: id||('t'+(++placedCounter)),
          tableNo, // ✅ store table number
          label: 'Table ' + tableNo,
          size,
          chairs,
          x,
          y,
          floor
        };

        (document._layout = document._layout || []).push(s);

        const el = makePlacedElement(s);
        canvas.appendChild(el);

        return el;
      }

     function addChairs(el, count){

  function render(){
    el.querySelectorAll('.chair').forEach(c => c.remove());

    const width = el.offsetWidth;
    const height = el.offsetHeight;

    const isCircle = el.classList.contains('table-small');

    // 🔥 CUSTOM TABLE LOGIC
if(s.chairs && !el.classList.contains('table-small') && !el.classList.contains('table-medium') && !el.classList.contains('table-large')){

  const sideChairs = 1; // left & right fixed
  const remaining = count - 2;

  let top = Math.ceil(remaining / 2);
  let bottom = Math.floor(remaining / 2);

  const spacing = 30; // 🔥 FIXED DISTANCE BETWEEN CHAIRS

  // 🔥 AUTO RESIZE TABLE WIDTH
  const neededWidth = Math.max(80, (Math.max(top, bottom) + 1) * spacing);
  el.style.width = neededWidth + 'px';

  const width = el.offsetWidth;
  const height = el.offsetHeight;

  // TOP
  for(let i=0;i<top;i++){
    const chair = document.createElement('div');
    chair.className = 'chair';

    chair.style.left = (width/2 - ((top-1)*spacing)/2 + i*spacing - 7) + 'px';
    chair.style.top = '-10px';
    chair.style.transform = 'rotate(180deg)';

    el.appendChild(chair);
  }

  // BOTTOM
  for(let i=0;i<bottom;i++){
    const chair = document.createElement('div');
    chair.className = 'chair';

    chair.style.left = (width/2 - ((bottom-1)*spacing)/2 + i*spacing - 7) + 'px';
    chair.style.top = (height - 4) + 'px';
    chair.style.transform = 'rotate(0deg)';

    el.appendChild(chair);
  }

  // LEFT (1 only)
  const leftChair = document.createElement('div');
  leftChair.className = 'chair';
  leftChair.style.left = '-10px';
  leftChair.style.top = (height/2 - 7) + 'px';
  leftChair.style.transform = 'rotate(90deg)';
  el.appendChild(leftChair);

  // RIGHT (1 only)
  const rightChair = document.createElement('div');
  rightChair.className = 'chair';
  rightChair.style.left = (width - 4) + 'px';
  rightChair.style.top = (height/2 - 7) + 'px';
  rightChair.style.transform = 'rotate(-90deg)';
  el.appendChild(rightChair);

  return; // 🚨 STOP here (skip other logic)
} else {
      if(el.classList.contains('table-large') || el.classList.contains('merged-group')){

  // 🔥 SMART DISTRIBUTION
  let top = Math.ceil(count * 0.35);
  let bottom = Math.ceil(count * 0.35);

  let remaining = count - (top + bottom);

  let left = Math.floor(remaining / 2);
  let right = remaining - left;

  const layout = [
    { side:'top', count:top },
    { side:'right', count:right },
    { side:'bottom', count:bottom },
    { side:'left', count:left },
  ];

  layout.forEach(pos=>{
    for(let i=0;i<pos.count;i++){

      const chair = document.createElement('div');
      chair.className = 'chair';

      if(pos.side === 'top'){
        chair.style.left = (width/(pos.count+1)*(i+1) - 7)+'px';
        chair.style.top = '-10px';
        chair.style.transform = 'rotate(180deg)';
      }

      if(pos.side === 'bottom'){
        chair.style.left = (width/(pos.count+1)*(i+1) - 7)+'px';
        chair.style.top = (height - 4)+'px';
        chair.style.transform = 'rotate(0deg)';
      }

      if(pos.side === 'left'){
        chair.style.left = '-10px';
        chair.style.top = (height/(pos.count+1)*(i+1) - 7)+'px';
        chair.style.transform = 'rotate(90deg)';
      }

      if(pos.side === 'right'){
        chair.style.left = (width - 4)+'px';
        chair.style.top = (height/(pos.count+1)*(i+1) - 7)+'px';
        chair.style.transform = 'rotate(-90deg)';
      }

      el.appendChild(chair);
    }
  });

} else  {
  // 🔁 DEFAULT LOGIC (for small & medium)
  const perSide = Math.ceil(count / 4);
  let placed = 0;

  const positions = ['top','right','bottom','left'];

  positions.forEach(side=>{
    for(let i=0;i<perSide && placed<count;i++,placed++){

      const chair = document.createElement('div');
      chair.className = 'chair';

      if(side === 'top'){
        chair.style.left = (width/(perSide+1)*(i+1) - 7)+'px';
        chair.style.top = '-10px';
        chair.style.transform = 'rotate(180deg)';
      }

      if(side === 'bottom'){
        chair.style.left = (width/(perSide+1)*(i+1) - 7)+'px';
        chair.style.top = (height - 4)+'px';
        chair.style.transform = 'rotate(0deg)';
      }

      if(side === 'left'){
        chair.style.left = '-10px';
        chair.style.top = (height/(perSide+1)*(i+1) - 7)+'px';
        chair.style.transform = 'rotate(90deg)';
      }

      if(side === 'right'){
        chair.style.left = (width - 4)+'px';
        chair.style.top = (height/(perSide+1)*(i+1) - 7)+'px';
        chair.style.transform = 'rotate(-90deg)';
      }

      el.appendChild(chair);
    }
  });
}
    }
  }

  render();

  new ResizeObserver(render).observe(el);
}
function attachCommonEvents(el, s){
  // selection
  el.addEventListener('click', e=>{
    if(!e.shiftKey){
      clearSelection();
    }

    if(selected.has(s.id)){
      selected.delete(s.id);
      el.classList.remove('selected');
    } else {
      selected.add(s.id);
      el.classList.add('selected');
    }

    selectedCount.textContent = selected.size;
    e.stopPropagation();
  });

  // drag inside canvas
  let offsetX=0, offsetY=0, dragging=false;

  el.addEventListener('mousedown', e=>{
    dragging=true;

    const rect = el.getBoundingClientRect();
    offsetX = e.clientX - rect.left;
    offsetY = e.clientY - rect.top;

    document.body.style.cursor='grabbing';
    e.stopPropagation();
  });

  window.addEventListener('mousemove', e=>{
    if(!dragging) return;

    const rect = canvas.getBoundingClientRect();

    const x = e.clientX - rect.left + canvas.scrollLeft - offsetX;
    const y = e.clientY - rect.top + canvas.scrollTop - offsetY;

    el.style.left = Math.max(0,x)+'px';
    el.style.top = Math.max(0,y)+'px';
  });

  window.addEventListener('mouseup', ()=>{
    if(dragging){
      dragging=false;
      document.body.style.cursor='';

      const rect = el.getBoundingClientRect();
      const cRect = canvas.getBoundingClientRect();

      const idx = (document._layout||[]).findIndex(o=>o.id===s.id);
      if(idx>=0){
        document._layout[idx].x = rect.left - cRect.left + canvas.scrollLeft;
        document._layout[idx].y = rect.top - cRect.top + canvas.scrollTop;
      }
    }
  });
}

      function makePlacedElement(s){
  const el = document.createElement('div');

  const sizeClass = getSizeClass(s.size);

  el.className = `placed-table ${sizeClass}`;

  el.style.left = s.x+'px';
  el.style.top = s.y+'px';

  el.dataset.id = s.id;
  el.dataset.floor = s.floor;

  // label
  el.innerHTML = `
  <span>${s.label}</span>
  <div class="resize-handle"></div>
`;

  // chairs
  setTimeout(()=>{
    if(s.chairs){
      addChairs(el, s.chairs);
    }
  },0);

  makeResizable(el, s);

  attachCommonEvents(el, s);

  return el;
}

function makeResizable(el, s){
  const handle = el.querySelector('.resize-handle');

  let resizing = false;
  let startX, startY, startW, startH;

  handle.addEventListener('mousedown', e=>{
    e.stopPropagation();
    resizing = true;

    startX = e.clientX;
    startY = e.clientY;

    startW = el.offsetWidth;
    startH = el.offsetHeight;

    document.body.style.cursor = 'nwse-resize';
  });

  window.addEventListener('mousemove', e=>{
    if(!resizing) return;

    let newW = startW + (e.clientX - startX);
    let newH = startH + (e.clientY - startY);

    // 🔥 LIMITS
    newW = Math.max(50, newW);
    newH = Math.max(50, newH);

    el.style.width = newW + 'px';
    el.style.height = newH + 'px';
  });

  window.addEventListener('mouseup', ()=>{
    if(resizing){
      resizing = false;
      document.body.style.cursor = '';

      // 🔥 SAVE SIZE
      const idx = (document._layout||[]).findIndex(o=>o.id===s.id);
      if(idx >= 0){
        document._layout[idx].w = el.offsetWidth;
        document._layout[idx].h = el.offsetHeight;
      }
    }
  });
}

      // click on empty canvas clears selection
      canvas.addEventListener('click', ()=>{ clearSelection(); selectedCount.textContent=0; });
      function clearSelection(){
        selected.forEach(id=>{ const el = canvas.querySelector('[data-id="'+id+'"]'); if(el) el.classList.remove('selected'); });
        selected.clear();
        selectedCount.textContent = 0;
      }

      // merge logic: compute bounding box, create merged-group element
      function mergeSelected(){
        let totalChairs = 0;
        let firstTableNo = null;
        if(selected.size<2) return alert('Select at least 2 tables (Shift+click) to merge');
        const ids = Array.from(selected);
        const elems = ids.map(id=>canvas.querySelector('[data-id="'+id+'"]')).filter(Boolean);
        if(elems.length<2) return alert('Could not find selected items on this floor');
        // compute bounds
        let minX=Infinity,minY=Infinity,maxX=-Infinity,maxY=-Infinity;
        elems.forEach(el=>{
          const r=el.getBoundingClientRect();
          const cr=canvas.getBoundingClientRect();
          const left = r.left - cr.left + canvas.scrollLeft;
          const top = r.top - cr.top + canvas.scrollTop;
          const data = (document._layout || []).find(o => o.id === el.dataset.id);

          if(data){
            if(firstTableNo === null){
              firstTableNo = data.tableNo; // ✅ first selected table
            }

            if(data.chairs){
              totalChairs += Number(data.chairs);
            }
          }
          minX = Math.min(minX,left); minY = Math.min(minY,top);
          maxX = Math.max(maxX,left + r.width); maxY = Math.max(maxY, top + r.height);
        });

        // remove items from DOM and _layout, but keep their ids inside group
        const groupId = 'g'+Date.now();
        const childData = [];
        elems.forEach(el=>{
          const data = (document._layout || []).find(o => o.id === el.dataset.id);
            if(data){
              childData.push({...data}); // keep full info
            }
          // remove from memory
          const idx = (document._layout||[]).findIndex(o=>o.id===el.dataset.id);
          if(idx>=0) document._layout.splice(idx,1);
          el.remove();
        });

        const groupObj = {
  type:'merged',
  id:groupId,
  x:minX,
  y:minY,
  w:maxX-minX,
  h:maxY-minY,
  children: childData,
  floor:activeFloor,
  label:'Table ' + firstTableNo, // ✅ USE FIRST TABLE NUMBER
  tableNo: firstTableNo,         // ✅ STORE IT
  chairs: totalChairs
};
        (document._layout = document._layout||[]).push(groupObj);
        const groupEl = makeMergedElement(groupObj);
        canvas.appendChild(groupEl);
        clearSelection();
        selectedCount.textContent=0;
      }

      function makeMergedElement(g){
  const el = document.createElement('div');

  const sizeClass = getSizeClass('large'); // merged always large

  el.className = `placed-table merged-group ${sizeClass}`;

  el.style.left = g.x+'px';
  el.style.top = g.y+'px';

  el.dataset.id = g.id;
  el.dataset.floor = g.floor;

  const base = 20;
  el.style.width = Math.max(g.w, g.chairs * base) + 'px';
  el.style.height = Math.max(g.h, g.chairs * 12) + 'px';

  el.innerHTML = `
  <span>${g.label}</span>
  <small style="position:absolute;bottom:4px;font-size:10px;color:#333;">
    ${g.chairs} seats
  </small>
`;

  setTimeout(()=>{
    if(g.chairs){
      addChairs(el, g.chairs);
    }
  },0);

  attachCommonEvents(el, g);

  return el;
}

      mergeBtn.addEventListener('click', mergeSelected);

      // unmerge: if a merged-group is selected, break into children
      unmergeBtn.addEventListener('click', ()=>{
        const ids = Array.from(selected);

        if(ids.length === 0){
          return alert('Select a merged group to unmerge');
        }

        for(const id of ids){
          const idx = (document._layout||[]).findIndex(o=>o.id===id && o.type==='merged');

          if(idx >= 0){
            const g = document._layout[idx];

            // remove group from memory
            document._layout.splice(idx,1);

            // remove DOM
            const el = canvas.querySelector('[data-id="'+id+'"]');
            if(el) el.remove();

            // recreate children
            const cols = Math.ceil(Math.sqrt(g.children.length));
            const rows = Math.ceil(g.children.length / cols);
            const cellW = g.w / cols;
            const cellH = g.h / rows;

            let i = 0;

            for(const child of g.children){
              const cx = g.x + (i % cols) * cellW + 6;
              const cy = g.y + Math.floor(i / cols) * cellH + 6;

              createPlaced({
                label: child.label,
                size: child.size,
                chairs: child.chairs,
                x: cx,
                y: cy,
                floor: g.floor,
                id: child.id
              });

              i++;
            }
          }
        }

        clearSelection();
      });

      clearBtn.addEventListener('click', ()=>{
        // remove all elements on current floor
        document._layout = (document._layout||[]).filter(o=>Number(o.floor)!==activeFloor);
        Array.from(canvas.children).forEach(c=>c.remove());
      });

      // add new template to left list
      addTableBtn.addEventListener('click', ()=>{
        const name = newTableName.value.trim() || ('Table '+(tableList.children.length+1));
        const size = newTableSize.value;
        const node = document.createElement('div');
        node.className='table-item'; node.draggable=true; node.dataset.size=size; node.dataset.label=name; node.innerHTML = name + ' <span class="badge">'+(size==='small'?'S':(size==='medium'?'M':'L'))+'</span>';
        tableList.appendChild(node);
        newTableName.value='';
      });

      // add floor
      addFloorBtn.addEventListener('click', ()=>{
        const id = Object.keys(floors).length + 1;
        floors[id] = {id:id,name:'Floor '+id};
        renderFloors();
      });

      // helper to create initial demo items on floor 1
      // function seedDemo(){
      //   createPlaced({label:'Table A',size:'medium',x:60,y:40,floor:1});
      //   createPlaced({label:'Table B',size:'small',x:220,y:40,floor:1});
      //   createPlaced({label:'Table C',size:'large',x:420,y:140,floor:1});
      // }

      // initial
      renderFloors(); 
      // seedDemo();

      // load layout when switching floor
      document._layout = document._layout || [];
      switchFloor(1);

      // small UX: delete with Delete key
      window.addEventListener('keydown', e=>{
        if(e.key==='Delete'){
          if(selected.size===0) return;
          for(const id of Array.from(selected)){
            const idx = (document._layout||[]).findIndex(o=>o.id===id);
            if(idx>=0) document._layout.splice(idx,1);
            const el = canvas.querySelector('[data-id="'+id+'"]'); if(el) el.remove();
          }
          clearSelection();
        }
      });

    })();


</script>
@endsection