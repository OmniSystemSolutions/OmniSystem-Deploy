@extends('layouts.app')
<script src="https://unpkg.com/timeago.js/dist/timeago.min.js"></script>

<style>
table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 10px 12px;
    vertical-align: middle !important;
}

thead {
    background-color: #e9ecf3;
    font-weight: bold;
}

tr {
    transition: background-color 0.3s ease;
}

tr:hover {
    background-color: #dcecff !important;
}

.btn {
    font-size: 0.85rem;
    padding: 4px 8px;
}

.fw-semibold {
    font-weight: 600;
}

.sortable {
  cursor: pointer;
  user-select: none;
}
.sortable:hover {
  background-color: #f8f9fa;
}

/* Wrapper keeps button + list together */
.recipe-wrapper {
  display: flex;
  flex-direction: column;
}

/* Recipe list styling */
.recipe-list {
  margin: 0;
  padding: 10px 14px;
  list-style-type: disc;
  background-color: #ffe5e5;
  border-radius: 6px;
  font-size: 0.9rem;
}

/* Optional spacing tweaks */
.recipe-list li {
  margin-bottom: 4px;
}

.recipe-name {
  font-weight: 600;
}

.recipe-qty {
  color: #555;
}

/* 🔥 Transition */
.slide-fade-enter-active,
.slide-fade-leave-active {
  transition: all 0.25s ease;
}

.slide-fade-enter,
.slide-fade-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}

/* ── Kitchen Card Grid ── */
.kitchen-cards {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 16px;
  padding: 8px 0 16px;
}

.k-card {
  background: #fff;
  border-radius: 10px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.10);
  overflow: hidden;
  display: flex;
  flex-direction: column;
  border: 1px solid #e5e7eb;
  transition: box-shadow 0.2s;
}
.k-card:hover {
  box-shadow: 0 4px 18px rgba(0,0,0,0.15);
}

/* coloured top stripe — colour comes from inline style */
.k-card__stripe {
  height: 6px;
  width: 100%;
}

.k-card__head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px 14px 4px;
}

.k-card__order {
  font-size: 1rem;
  font-weight: 700;
  color: #1565c0;
}

.k-card__qty {
  background: #1565c0;
  color: #fff;
  border-radius: 20px;
  padding: 2px 10px;
  font-size: 0.85rem;
  font-weight: 700;
}

.k-card__name {
  padding: 4px 14px 2px;
  font-size: 1rem;
  font-weight: 700;
  color: #111;
  line-height: 1.3;
}

.k-card__sku {
  padding: 0 14px 8px;
  font-size: 0.78rem;
}

.k-card__meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 6px 14px;
  background: #f8f9fa;
  border-top: 1px solid #f0f0f0;
  font-size: 0.8rem;
  gap: 6px;
}

.k-card__station {
  color: #555;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.k-card__time {
  white-space: nowrap;
  font-size: 0.82rem;
}

.k-card__cook {
  padding: 4px 14px;
  font-size: 0.8rem;
}

/* ── Recipe chip block ── */
.k-recipe {
  padding: 8px 14px 10px;
  border-top: 1px solid #f0f0f0;
  background: #fafafa;
}
.k-recipe__label {
  font-size: 0.68rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: #999;
  margin-bottom: 6px;
}
.k-recipe__chips {
  display: flex;
  flex-wrap: wrap;
  gap: 5px;
}
.k-chip {
  display: inline-flex;
  align-items: center;
  border-radius: 999px;
  overflow: hidden;
  font-size: 0.75rem;
  line-height: 1;
  box-shadow: 0 1px 3px rgba(0,0,0,0.08);
}
.k-chip__name {
  background: #e8f0fe;
  color: #1a56db;
  font-weight: 600;
  padding: 4px 8px 4px 10px;
}
.k-chip__qty {
  background: #1a56db;
  color: #fff;
  font-weight: 700;
  padding: 4px 9px 4px 7px;
}

.k-card__recipe-wrap {
  padding: 0 14px 4px;
}

.k-card__actions {
  padding: 0 14px 12px;
}

.k-card__item-count {
  font-size: 0.75rem;
  color: #9ca3af;
  font-weight: 600;
}

/* ── Per-item rows inside a grouped card ── */
.k-item {
  padding: 10px 14px 6px;
}
.k-item--bordered {
  border-top: 1px dashed #e5e7eb;
}
.k-item__top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
}
.k-item__left {
  display: flex;
  align-items: center;
  gap: 8px;
  flex: 1;
  min-width: 0;
}
.k-item__name {
  font-size: 0.92rem;
  font-weight: 700;
  color: #111827;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.k-item__qty {
  background: #f3f4f6;
  color: #374151;
  font-weight: 700;
  font-size: 0.75rem;
  padding: 2px 8px;
  border-radius: 999px;
  white-space: nowrap;
  flex-shrink: 0;
}
.k-item__action { flex-shrink: 0; }
.k-item__btn-update {
  background: #ff630f;
  color: #fff;
  border: none;
  font-size: 0.72rem;
  font-weight: 700;
  padding: 4px 12px;
  border-radius: 6px;
  white-space: nowrap;
}
.k-item__btn-update:hover { box-shadow: 0 6px 18px rgba(0,0,0,0.15); }
.k-item__btn-push {
  background: #ff9800;
  color: #fff;
  border: none;
  font-size: 0.72rem;
  font-weight: 700;
  padding: 4px 12px;
  border-radius: 6px;
  white-space: nowrap;
}
.k-item__btn-push:hover { background: #e65100; color: #fff; }
.k-item__cook {
  font-size: 0.75rem;
  white-space: nowrap;
}
.k-recipe--item {
  padding: 5px 0 4px;
  background: transparent;
  border-top: none;
}

/* ── Update Status Modal ── */
.um-modal { border: none; border-radius: 14px; overflow: hidden; }

.um-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  background: linear-gradient(135deg, #1a56db 0%, #1e3a8a 100%);
  padding: 20px 24px;
  color: #fff;
}
.um-header__mode {
  font-size: 0.72rem;
  font-weight: 700;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  opacity: 0.75;
  margin-bottom: 3px;
}
.um-header__order {
  font-size: 1.25rem;
  font-weight: 700;
}
.um-header__order span { color: #93c5fd; }

.um-info-strip {
  display: flex;
  flex-wrap: wrap;
  gap: 0;
  background: #f0f4ff;
  border-bottom: 1px solid #dde5f7;
}
.um-info-strip__item {
  flex: 1 1 25%;
  min-width: 140px;
  padding: 12px 20px;
  border-right: 1px solid #dde5f7;
}
.um-info-strip__item:last-child { border-right: none; }
.um-info-strip__label {
  font-size: 0.68rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: #6b7280;
  margin-bottom: 4px;
}
.um-info-strip__value {
  font-size: 0.92rem;
  font-weight: 600;
  color: #111827;
}

.um-field-label {
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  color: #6b7280;
  margin-bottom: 6px;
  display: block;
}

.um-section-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 12px;
  font-size: 0.85rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.07em;
  color: #374151;
}
.um-toggle-loss {
  display: flex;
  align-items: center;
  gap: 6px;
  cursor: pointer;
  font-size: 0.78rem;
  font-weight: 600;
  text-transform: none;
  letter-spacing: 0;
  color: #1a56db;
}
.um-toggle-loss input { cursor: pointer; accent-color: #1a56db; }

.um-ingredient-list {
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  overflow: hidden;
}
.um-ingredient-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 16px;
  border-bottom: 1px solid #f3f4f6;
  background: #fff;
  transition: background 0.15s;
}
.um-ingredient-row:last-child { border-bottom: none; }
.um-ingredient-row:hover { background: #f9fafb; }
.um-ingredient-row__info {
  display: flex;
  flex-direction: column;
}
.um-ingredient-row__name {
  font-size: 0.9rem;
  font-weight: 600;
  color: #111827;
}
.um-ingredient-row__qty {
  font-size: 0.78rem;
  color: #6b7280;
  margin-top: 1px;
}
.um-ingredient-row__loss {
  display: flex;
  align-items: center;
  gap: 8px;
}
.um-ingredient-row__loss .form-select,
.um-ingredient-row__loss .form-control { width: auto; min-width: 100px; }
.um-ingredient-row__unit {
  font-size: 0.78rem;
  color: #6b7280;
  white-space: nowrap;
}

.um-footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  padding: 16px 24px;
  border-top: 1px solid #e5e7eb;
  background: #f9fafb;
}

</style>
@section('content')
<div class="main-content" id="app">
  <div>
      <div class="breadcrumb">
          <h1 class="mr-3">POS</h1>
          <ul>
          <li><a href=""> Kitchen </a></li>
          <!----> <!---->
          </ul>
          <div class="breadcrumb-action"></div>
      </div>
      <div class="separator-breadcrumb border-top"></div>
  </div>
  <!-- Update Status Modal -->
  <div class="modal fade" id="updateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content um-modal" v-if="selectedOrder">

        <!-- Header -->
        <div class="um-header">
          <div>
            <div class="um-header__mode">@{{ modalMode === 'push' ? 'Push Item' : 'Update Status' }}</div>
            <div class="um-header__order">Order <span>#@{{ selectedOrder.order_no }}</span></div>
          </div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>

        <!-- Body -->
        <div class="modal-body p-0">
          <form @submit.prevent="submitUpdateStatus">

            <!-- Order Info strip -->
            <div class="um-info-strip">
              <div class="um-info-strip__item">
                <div class="um-info-strip__label">Order No</div>
                <template v-if="modalMode === 'push'">
                  <v-select :options="availableOrders" label="order_no" :reduce="o => o.id"
                    v-model="selectedOrder.new_order_id" placeholder="Select Order" :clearable="false" />
                </template>
                <template v-else>
                  <div class="um-info-strip__value">#@{{ selectedOrder.order_no }}</div>
                </template>
              </div>
              <div class="um-info-strip__item">
                <div class="um-info-strip__label">Product</div>
                <div class="um-info-strip__value">@{{ selectedOrder.name }}</div>
              </div>
              <div class="um-info-strip__item">
                <div class="um-info-strip__label">Time Ordered</div>
                <div class="um-info-strip__value">@{{ formatTime(selectedOrder.time_submitted) }}</div>
              </div>
              <div class="um-info-strip__item">
                <div class="um-info-strip__label">Station</div>
                <div class="um-info-strip__value">@{{ selectedOrder.station }}</div>
              </div>
            </div>

            <!-- Fields -->
            <div class="row g-3 px-4 pt-4 pb-3">
              <div class="col-md-6">
                <label class="um-field-label">Chef / Cook</label>
                <v-select :options="chefs" label="name" :reduce="chef => chef.id"
                  v-model="selectedOrder.cook_id" placeholder="-- Select Cook --" :clearable="true" />
              </div>
              <div class="col-md-6">
                <label class="um-field-label">Status</label>
                <input v-if="modalMode === 'push'" type="text" class="form-control" value="Served" readonly>
                <v-select v-else
                  :options="[{label:'Served',value:'served'},{label:'Walked',value:'walked'},{label:'Cancelled',value:'cancelled'}]"
                  label="label" :reduce="s => s.value" v-model="selectedOrder.status"
                  placeholder="Select Status" :clearable="false" />
              </div>
            </div>

            <!-- Ingredients section -->
            <div v-if="selectedOrder && modalMode !== 'push'" class="px-4 pb-4">

              <div class="um-section-head">
                <span>Ingredients</span>
                <label class="um-toggle-loss">
                  <input type="checkbox" v-model="selectedOrder.showLoss">
                  <span class="um-toggle-loss__text">Log Wastage</span>
                </label>
              </div>

              <!-- ingredient rows -->
              <div class="um-ingredient-list">
                <div v-if="!selectedOrder.recipe || selectedOrder.recipe.length === 0"
                  class="text-center text-muted py-3" style="font-size:0.9rem;">No ingredients found.</div>

                <div v-for="(ingredient, index) in selectedOrder.recipe" :key="index"
                  class="um-ingredient-row" :class="{ 'um-ingredient-row--loss': selectedOrder.showLoss }">

                  <!-- left: name + base qty -->
                  <div class="um-ingredient-row__info">
                    <span class="um-ingredient-row__name">@{{ ingredient.component_name }}</span>
                    <span class="um-ingredient-row__qty">@{{ ingredient.quantity }} @{{ ingredient.unit }}</span>
                  </div>

                  <!-- right: loss fields (only when toggled) -->
                  <template v-if="selectedOrder.showLoss">
                    <div class="um-ingredient-row__loss">
                      <select v-model="ingredient.loss_type" class="form-select form-select-sm">
                        <option disabled value="">Loss Type</option>
                        <option value="wastage">Wastage</option>
                        <option value="spoilage">Spoilage</option>
                        <option value="theft">Theft</option>
                      </select>
                      <input type="number" v-model.number="ingredient.loss_qty" step="0.01" min="0"
                        class="form-control form-control-sm" placeholder="Qty">
                      <span class="um-ingredient-row__unit">@{{ ingredient.unit }}</span>
                    </div>
                  </template>
                </div>
              </div>
            </div>

            <!-- Footer buttons -->
            <div class="um-footer">
              <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary px-5">Submit</button>
            </div>
          </form>
        </div>

      </div>
    </div>
  </div>
  <div class="row">
                    <div class="col-sm-12 col-md-3">
                      <fieldset class="form-group">
                          <legend class="col-form-label pt-0">Year</legend>
                          <v-select
                          v-model="selectedYear"
                          :options="years"
                          :clearable="false"
                          label="label"
                          ></v-select>
                      </fieldset>
                    </div>

                    <div class="col-sm-12 col-md-3">
                      <fieldset class="form-group">
                          <legend class="col-form-label pt-0">Month</legend>
                          <v-select
                          v-model="selectedMonth"
                          :options="monthOptions"
                          label="label"
                          :reduce="m => m.value"
                          :clearable="false"
                          ></v-select>
                      </fieldset>
                    </div>

                    <div class="col-sm-12 col-md-3">
                      <fieldset class="form-group">
                        <legend class="col-form-label pt-0">Day</legend>
                        <v-select
                          v-model="selectedDay"
                          :options="daysInMonth"
                          :clearable="false"
                        ></v-select>
                      </fieldset>
                    </div>

                    <div class="col-md-3 mt-3 mt-md-4">
                      <button class="btn btn-primary w-100" @click="resetToToday">Today’s Orders</button>
                    </div>
                  </div>

    <div class="wrapper">
        <div class="card mt-4">
            <nav class="card-header d-flex align-items-center justify-content-between">
                <ul class="nav nav-tabs card-header-tabs">
                  <li class="nav-item" v-for="status in statusList" :key="status.value">
                      <a href="#"
                        class="nav-link"
                        :class="{ active: statusFilter === status.value }"
                        @click.prevent="setStatus(status.value)">
                      @{{ status.label }}
                      </a>
                  </li>
                </ul>
                <div class="btn-group ms-auto" style="flex-shrink:0;">
                  <button type="button"
                    class="btn btn-sm"
                    :class="viewMode === 'table' ? 'btn-primary' : 'btn-outline-secondary'"
                    @click="viewMode = 'table'"
                    title="Table view">
                    <i class="i-Table2"></i>
                    Default
                  </button>
                  <button type="button"
                    class="btn btn-sm"
                    :class="viewMode === 'card' ? 'btn-primary' : 'btn-outline-secondary'"
                    @click="viewMode = 'card'"
                    title="Card view">
                    <i class="i-Postcard"></i>
                      Card
                  </button>
                </div>
            </nav>

            <div class="row" v-if="statusFilter === 'serving'">
  <!-- ALL Stations Button -->
  <div class="col-6 col-md-3 mt-3 mt-md-4">
    <button 
      class="btn w-100 m-3"
      :class="selectedStation === null ? 'btn-primary' : 'btn-outline-primary'"
      @click="showAllStations"
    >
      All Stations
    </button>
  </div>

  <!-- Dynamic Stations -->
  <div 
    v-for="station in stations" 
    :key="station.id" 
    class="col-6 col-md-3 mt-3 mt-md-4"
  >
    <button 
      class="btn w-100 mt-3"
      :class="selectedStation === station.id ? 'btn-primary' : 'btn-outline-primary'"
      @click="selectStation(station)"
    >
      @{{ station.name }}
    </button>
  </div>
</div>


            <div class="card-body">

                {{-- ── GROUPED CARD VIEW ── --}}
                <div v-if="viewMode === 'card'" class="kitchen-cards">
                  <p v-if="groupedOrders.length === 0" class="text-center text-muted py-4">No data available.</p>

                  <div v-for="group in groupedOrders" :key="group.order_no" class="k-card">

                    {{-- Stripe --}}
                    <div class="k-card__stripe" :style="{ background: stripeColor(group.created_at) }"></div>

                    {{-- Card header: Order # + timer --}}
                    <div class="k-card__head">
                      <span class="k-card__order">#@{{ group.order_no }}</span>
                      <span class="k-card__time fw-bold"
                        :class="{ 'text-danger': (new Date(now) - new Date(group.created_at)) / 60000 >= 15 }">
                        <i class="i-Clock me-1"></i>
                        @{{ group.items[0].status === 'serving' ? getRunningTime(group.created_at) : formatAMPM(group.created_at) }}
                      </span>
                    </div>

                    {{-- Divider --}}
                    <div style="height:1px;background:#f0f0f0;margin:0 14px;"></div>

                    {{-- Item rows --}}
                    <div v-for="(item, idx) in group.items" :key="item.order_detail_id"
                      class="k-item" :class="{ 'k-item--bordered': idx > 0 }">

                      {{-- Item name + qty + action button --}}
                      <div class="k-item__top">
                        <div class="k-item__left">
                          <span class="k-item__name">@{{ item.name }}</span>
                          <span class="k-item__qty">×@{{ item.qty }}</span>
                        </div>
                        <div class="k-item__action">
                          <button v-if="statusFilter === 'serving'"
                            class="btn btn-sm k-item__btn-update"
                            @click="openUpdateModal(item)">
                            Update
                          </button>
                          <button v-if="statusFilter === 'walked'"
                            class="btn btn-sm k-item__btn-push"
                            @click="pushItem(item)">
                            Push
                          </button>
                          <span v-if="statusFilter !== 'serving' && statusFilter !== 'walked'"
                            class="k-item__cook text-muted">
                            @{{ item.cook_name || '—' }}
                          </span>
                        </div>
                      </div>

                      {{-- Recipe chips --}}
                      <div v-if="item.recipe && item.recipe.length" class="k-recipe k-recipe--item">
                        <div class="k-recipe__chips">
                          <span v-for="r in item.recipe" :key="r.component_id || r.component_name" class="k-chip">
                            <span class="k-chip__name">@{{ r.component_name }}</span>
                            <span class="k-chip__qty">@{{ r.quantity }}</span>
                          </span>
                        </div>
                      </div>
                    </div>

                    {{-- Card footer: station --}}
                    <div class="k-card__meta">
                      <span class="k-card__station">
                        <i class="i-Tag me-1"></i>@{{ group.items[0].station }}
                      </span>
                      <span class="k-card__item-count">@{{ group.items.length }} item@{{ group.items.length > 1 ? 's' : '' }}</span>
                    </div>

                  </div>
                </div>

                {{-- ── TABLE VIEW ── --}}
                <div class="vgt-wrap" v-if="viewMode === 'table'">


                <div class="vgt-inner-wrap">
                    <div class="vgt-fixed-header">
                        <!---->
                    </div>
                    <div class="vgt-responsive"style="max-height: 400px; overflow-y: auto;">
                        <table id="vgt-table" class="table-hover tableOne vgt-table custom-vgt-table ">
                            <colgroup>
                            <col id="col-0">
                            <col id="col-1">
                            <col id="col-2">
                            <col id="col-3">
                            <col id="col-4">
                            <col id="col-5">
                            <col id="col-6">
                            <col id="col-7">
                            <col id="col-8">
                            <col id="col-9">
                            <col id="col-10">
                            <col id="col-11">
                            <col id="col-12">
                            <col id="col-13">
                            <col id="col-14">
                            <col id="col-15">
                            <col id="col-16">
                            </colgroup>
                            <thead style="min-width: auto; width: auto;">
                            <tr>
                              <th scope="col" class="vgt-left-align text-left sortable" @click="sortTable('order_no')">
                                <span>Order No.</span>
                                <i :class="getSortIcon('order_no')" class="ms-1"></i>
                              </th>

                              <th scope="col" class="vgt-left-align text-left sortable" @click="sortTable('time_submitted')">
                                <span>Time Ordered</span>
                                <i :class="getSortIcon('time_submitted')" class="ms-1"></i>
                              </th>

                              <th scope="col" class="vgt-left-align text-left sortable" @click="sortTable('code')">
                                <span>SKU</span>
                                <i :class="getSortIcon('code')" class="ms-1"></i>
                              </th>

                              <th scope="col" class="vgt-left-align text-left sortable" @click="sortTable('name')">
                                <span>Product Name</span>
                                <i :class="getSortIcon('name')" class="ms-1"></i>
                              </th>

                              <th scope="col" class="vgt-left-align text-left sortable" @click="sortTable('qty')">
                                <span>Qty</span>
                                <i :class="getSortIcon('qty')" class="ms-1"></i>
                              </th>

                              <th scope="col" class="vgt-left-align text-left sortable" @click="sortTable('category')">
                                <span>Category</span>
                                <i :class="getSortIcon('category')" class="ms-1"></i>
                              </th>

                              <th scope="col" class="vgt-left-align text-left sortable" @click="sortTable('station')">
                                <span>Station</span>
                                <i :class="getSortIcon('station')" class="ms-1"></i>
                              </th>

                              <th scope="col" class="vgt-left-align text-left sortable"  @click="sortTable('time_submitted')">
                                <span>@{{ dynamicHeaderLabel }}</span>
                                <i :class="getSortIcon('time_submitted')" class="ms-1"></i>
                              </th>

                              <th
                                v-if="statusFilter !== 'serving'"
                                scope="col"
                                class="vgt-left-align text-left sortable"
                                @click="sortTable('cook_name')"
                              >
                                <span>Chef, Cook</span>
                                <i :class="getSortIcon('cook_name')" class="ms-1"></i>
                              </th>

                              {{-- <th scope="col" class="vgt-left-align text-right sortable" @click="sortTable('running_time')">
                                <span>Running Time</span>
                                <i :class="getSortIcon('running_time')" class="ms-1"></i>
                              </th> --}}

                              <th scope="col" class="vgt-left-align text-left sortable" v-if="statusFilter == 'serving'">
                                <span>Recipe</span>
                              </th>

                              <th 
                                v-if="statusFilter === 'serving' || statusFilter === 'walked'" 
                                scope="col" 
                                class="vgt-left-align text-left">
                                <span>Action</span>
                              </th>
                            </tr>

                            </thead>
                            <tbody>
                              <tr
  v-for="(item, index) in filteredOrders"
  :key="index"
  :style="statusFilter === 'serving'
    ? { backgroundColor: getOrderColor(item.created_at) }
    : {}"
>
                                <td class="text-left fw-bold text-primary">#@{{ item.order_no }}</td>
                                <td class="text-left">@{{ formatAMPM(item.created_at) }}</td>
                                <td class="text-left fw-semibold">@{{ item.code }}</td>
                                <td class="text-left">@{{ item.name }}</td>
                                <td class="text-end">@{{ item.qty }}</td>
                                <td class="text-end">@{{ item.category }}/@{{ item.subcategory }}</td>
                                <td class="text-end">@{{ item.station }}</td>
                                <td class="text-end fw-bold"
                                    :class="statusFilter === 'serving' 
                                    ?{ 'text-danger': (new Date(now) - new Date(item.created_at)) / 60000 >= 15 }
                                    : {}">

                                  @{{ item.status === 'serving'
                                      ? getRunningTime(item.created_at)
                                      : formatAMPM(item.created_at)
                                  }}

                                </td>

<td v-if="statusFilter == 'serving'">
  <div class="recipe-wrapper">
    <button
      class="btn btn-primary w-100 mb-2"
      @click="expandedOrderId = expandedOrderId === item.order_detail_id ? null : item.order_detail_id"
    >
      @{{ expandedOrderId === item.order_detail_id ? 'Hide Recipe' : 'View Recipe' }}
    </button>

    <transition name="slide-fade">
      <ul
        v-show="expandedOrderId === item.order_detail_id"
        class="recipe-list"
      >
        <li
          v-for="r in item.recipe"
          :key="r.component_id || r.component_name"
        >
          <span class="recipe-name">@{{ r.component_name }}</span>
          <span class="recipe-qty">— @{{ r.quantity }}</span>
        </li>
      </ul>
    </transition>
  </div>
</td>

<td v-else>
  @{{ item.cook_name || 'N/A' }}
</td>



                                <td class="text-left" v-if="statusFilter === 'serving' || statusFilter === 'walked'">
                                  <div class="dropdown b-dropdown btn-group">
                                    <button id="dropdownMenu{{ $id ?? uniqid() }}"
                                        type="button"
                                        class="btn dropdown-toggle btn-link btn-lg text-decoration-none dropdown-toggle-no-caret"
                                        data-bs-toggle="dropdown"
                                        aria-haspopup="true"
                                        aria-expanded="false">
                                        <span class="_dot _r_block-dot bg-dark"></span>
                                        <span class="_dot _r_block-dot bg-dark"></span>
                                        <span class="_dot _r_block-dot bg-dark"></span>
                                    </button>

                                    <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu">
                                       <!-- Update Status (only serving) -->
                                        <li v-if="statusFilter === 'serving'" role="presentation">
                                          <a
                                            class="dropdown-item"
                                            href="#"
                                            @click="openUpdateModal(item)"
                                          >
                                            <i class="nav-icon i-Edit font-weight-bold mr-2"></i>
                                            Update Status
                                          </a>
                                        </li>

                                        <!-- Remarks (only serving) -->
                                        <li v-if="statusFilter === 'serving'" role="presentation">
                                          <a class="dropdown-item" href="#">
                                            <i class="nav-icon i-Mail-Attachement font-weight-bold mr-2"></i>
                                            Remarks
                                          </a>
                                        </li>

                                        <!-- Push Item (only walked) -->
                                        <li v-if="statusFilter === 'walked'" role="presentation">
                                          <a
                                            class="dropdown-item"
                                            href="#"
                                            @click="pushItem(item)"
                                          >
                                            <i class="nav-icon i-Upload font-weight-bold mr-2"></i>
                                            Push Item
                                          </a>
                                        </li>
                                    </ul>
                                  </div>
                                </td>
                              </tr>
                              <tr v-if="filteredOrders.length === 0">
                                  <td colspan="9" class="text-center text-muted">No data available.</td>
                              </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                </div>{{-- end v-if table --}}
            </div>
        </div>
    </div>
</div>
<script>
  Vue.component('v-select', VueSelect.VueSelect);
new Vue({
  el: "#app",
  data: {
    now: new Date(), // reactive timestamp that updates every second
    viewMode: 'table', // 'table' | 'card'
    selectedOrder: null,
    modalMode: null,
    orderItems: [],
    items: [],
    expandedOrderId: null,
    selectedStation: null,
    chefs: [],
    availableOrders: [],
    stations: [],
    branchProducts: [],
    branchComponents: [],
    headerLabelMap: {
        pending: 'Running Time',
        served: 'Time Served',
        walked: 'Time Served',
        cancelled: 'Time Cancelled',
    },
    rowFieldMap: {
        pending: 'time_submitted',
        served: 'time_submitted',
        walked: 'time_submitted',
        cancelled: 'time_submitted',
    },
    statusFilter: 'serving',
    statusList: [
        { label: 'Preparing', value: 'serving' },
        { label: 'Served', value: 'served' },
        { label: 'Walked', value: 'walked' },
        { label: 'Cancelled', value: 'cancelled' },
    ],

    // 🔹 Date filter state
    selectedYear: new Date().getFullYear(),
    selectedMonth: new Date().getMonth() + 1,
    selectedDay: new Date().getDate(),
    months: [
      "January", "February", "March", "April", "May", "June",
      "July", "August", "September", "October", "November", "December"
    ],
    sortKey: '',
    sortAsc: true,
  },

  computed: {
    dynamicHeaderLabel() {
                return this.headerLabelMap[this.statusFilter] || 'Running Time';
            },
            dynamicRowField() {
                return this.rowFieldMap[this.statusFilter] || '-';
            },
    // Generate list of last 5 years
    years() {
      const current = new Date().getFullYear();
      return Array.from({ length: 5 }, (_, i) => current - i);
    },
  monthOptions() {
    return this.months.map((m, i) => ({
      label: m,
      value: i + 1
    }));
  },

    // Generate days for selected month/year
    daysInMonth() {
      return Array.from(
        { length: new Date(this.selectedYear, this.selectedMonth, 0).getDate() },
        (_, i) => i + 1
      );
    },

    // 🔹 Filtered + Sorted orders based on selected date
filteredOrders() {
  // Backend already filters by date; just sort here
  let data = [...this.orderItems];

  if (this.sortKey) {
  data = [...data].sort((a, b) => {
    let valA = a[this.sortKey];
    let valB = b[this.sortKey];

    // 🕐 Special: sort running time based on difference from now
    if (this.sortKey === 'running_time') {
      const diffA = new Date(this.now) - new Date(a.created_at);
      const diffB = new Date(this.now) - new Date(b.created_at);
      return this.sortAsc ? diffA - diffB : diffB - diffA;
    }

    // 🕐 Special: sort by actual submission time
    if (this.sortKey === 'time_submitted') {
      return this.sortAsc
        ? new Date(valA) - new Date(valB)
        : new Date(valB) - new Date(valA);
    }

    // Numeric comparison (qty, etc.)
    if (!isNaN(valA) && !isNaN(valB)) {
      return this.sortAsc ? valA - valB : valB - valA;
    }

    // Default string comparison
    return this.sortAsc
      ? String(valA).localeCompare(String(valB))
      : String(valB).localeCompare(String(valA));
  });
}


  return data;
},

groupedOrders() {
  const map = {};
  this.filteredOrders.forEach(item => {
    const key = item.order_no;
    if (!map[key]) {
      map[key] = { order_no: key, created_at: item.created_at, items: [] };
    }
    map[key].items.push(item);
  });
  return Object.values(map).sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
},

  },

  watch: {
    'selectedOrder.new_order_id'(val) {
    console.log('New Order Selected:', val);
  },
  statusFilter() {
    this.fetchItems()
  },
  selectedYear() {
    this.fetchItems()
  },
  selectedMonth() {
    this.fetchItems()
  },
  selectedDay() {
    this.fetchItems()
  }
},

  mounted() {
    // 🕒 Update timer every second
    setInterval(() => {
      this.now = new Date();
    }, 1000);
    this.fetchItems()
  },

  methods: {
    isServedOrWalked(item) {
    return ['served', 'walked'].includes(item.status);
  },
  formatAMPM(time) {
    if (!time) return 'N/A'
    return new Date(time).toLocaleTimeString('en-US', {
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit',
      hour12: true
    })
  },
    fetchItems() {
  const currentExpandedId = this.expandedOrderId; // remember which recipe was expanded

  axios.get('/kitchen/fetch', {
    params: {
      status: this.statusFilter,
      year: this.selectedYear,
      month: this.selectedMonth,
      day: this.selectedDay,
      station_id: this.selectedStation
    }
  }).then(res => {
    this.orderItems       = res.data.orderItems;
    this.availableOrders  = res.data.availableOrders;
    this.chefs            = res.data.chefs;
    this.stations         = res.data.stations;
    this.branchProducts   = res.data.branchProducts;
    this.branchComponents = res.data.branchComponents;
    console.log('details:', this.orderItems);
    // Restore expanded recipe if it still exists
    if (currentExpandedId && this.orderItems.some(i => i.order_detail_id === currentExpandedId)) {
      this.expandedOrderId = currentExpandedId;
    } else {
      this.expandedOrderId = null;
    }

    console.log('data today:', this.orderItems);
  }).catch(err => console.error('Failed to fetch items:', err));
},
selectStation(station) {
  this.selectedStation = station.id;
  this.fetchItems();
},

showAllStations() {
  this.selectedStation = null;
  this.fetchItems();
},
setStatus(status) {
               this.statusFilter = status;
   
                // ✅ Remember last opened tab
              //  localStorage.setItem('inventory_transfer_last_tab', status);
              //  this.fetchRecords(1);
           },
    // Reset dropdowns to today's date
    resetToToday() {
  const now = new Date()
  this.selectedYear = now.getFullYear()
  this.selectedMonth = now.getMonth() + 1
  this.selectedDay = now.getDate()
  this.fetchItems()
},

    // 🕐 Compute live running time in H:M:S format
    getRunningTime(submitted) {
      const diffInSeconds = Math.floor((new Date(this.now) - new Date(submitted)) / 1000);
      if (diffInSeconds < 0) return "0s"; // safeguard

      const hours = Math.floor(diffInSeconds / 3600);
      const mins = Math.floor((diffInSeconds % 3600) / 60);
      const secs = diffInSeconds % 60;

      let timeStr = "";
      if (hours > 0) timeStr += `${hours}h `;
      if (mins > 0 || hours > 0) timeStr += `${mins}m `;
      timeStr += `${secs}s`;
      return timeStr.trim();
    },

    // 🟩🟧🟥 Compute background color based on elapsed time
    getOrderColor(submitted) {
      const diffInMinutes = (new Date(this.now) - new Date(submitted)) / 1000 / 60;
      if (diffInMinutes >= 15) return '#ffcccc';
      if (diffInMinutes >= 10) return '#ffe5b4';
      if (diffInMinutes >= 5)  return '#e8f5e9';
      return '#ffffff';
    },

    stripeColor(submitted) {
      const diffInMinutes = (new Date(this.now) - new Date(submitted)) / 1000 / 60;
      if (diffInMinutes >= 15) return '#f44336';
      if (diffInMinutes >= 10) return '#ff9800';
      if (diffInMinutes >= 5)  return '#4caf50';
      return '#90a4ae';
    },

    formatTime(datetime) {
      const local = new Date(datetime + 'Z');
      return local.toLocaleTimeString('en-PH', {
        timeZone: 'Asia/Manila',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: true
      });
    },

    openUpdateModal(item, mode = 'update') {
  this.modalMode = mode;

  // Deep copy
  this.selectedOrder = JSON.parse(JSON.stringify(item || {}));

  // Defaults
  this.selectedOrder.showLoss = false;
  this.selectedOrder.recipe = this.selectedOrder.recipe || [];

  // 🔥 If Push Mode
  if (this.modalMode === 'push') {
    this.selectedOrder.status = 'served';
    this.selectedOrder.order_no = null; // force select
  } else {
    this.selectedOrder.status = null; // allow placeholder
  }

  this.$nextTick(() => {
    const modalEl = document.getElementById('updateModal');

    let modal = bootstrap.Modal.getInstance(modalEl);

    if (!modal) {
      modal = new bootstrap.Modal(modalEl, {
        backdrop: 'static',
        keyboard: false
      });
    }

    modal.show();
  });
},
pushItem(item) {
  this.openUpdateModal(item, 'push');
},


resetUpdateModal() {
  this.selectedOrder = null;
  this.modalMode = null;
},

    sortTable(key) {
    if (this.sortKey === key) {
      this.sortAsc = !this.sortAsc; // toggle ascending/descending
    } else {
      this.sortKey = key;
      this.sortAsc = true; // default ascending
    }
  },
  getSortIcon(key) {
    if (this.sortKey !== key) return 'fa fa-sort text-muted';
    return this.sortAsc ? 'fa fa-sort-up text-primary' : 'fa fa-sort-down text-primary';
  },

    // fetchOrders() {
    //   axios.get(`/kitchen/served`)
    //     .then(res => {
    //       this.orderItems = res.data.orderItems;
    //     })
    //     .catch(err => console.error("❌ Failed to reload orders:", err));
    // },

    async submitUpdate() {
  // 🔒 Show loader immediately
  Swal.fire({
    title: 'Updating order...',
    html: 'Please wait',
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();
    }
  });

  try {
    const now = new Date();
    const timeSubmitted =
      now.getFullYear() + '-' +
      String(now.getMonth() + 1).padStart(2, '0') + '-' +
      String(now.getDate()).padStart(2, '0') + ' ' +
      now.toLocaleTimeString('en-US', { hour12: false });

    // ✅ Prepare deductions
    const deductions = [];
    if (['served', 'walked'].includes(this.selectedOrder.status)) {
      const recipes = this.selectedOrder.recipe || [];

      recipes.forEach(ingredient => {
        const usedQty = ingredient.quantity || 0;
        const lossQty = ingredient.loss_qty || 0;

        if (usedQty > 0) {
          deductions.push({
            component_id: ingredient.component_id,
            order_detail_id: this.selectedOrder.order_detail_id,
            quantity_deducted: usedQty,
            deduction_type: 'served',
            notes: `Used for order (${this.selectedOrder.status}).`,
          });
        }

        if (lossQty > 0) {
          const mappedType = ['wastage', 'spoilage', 'theft'].includes(
            (ingredient.loss_type || '').toLowerCase()
          )
            ? ingredient.loss_type.toLowerCase()
            : 'wastage';

          deductions.push({
            component_id: ingredient.component_id,
            order_detail_id: this.selectedOrder.order_detail_id,
            quantity_deducted: lossQty,
            deduction_type: mappedType,
            notes: `Wasted due to ${mappedType}.`,
          });
        }
      });
    }

    const payload = {
      order_detail_id: this.selectedOrder.order_detail_id,
      cook_id: this.selectedOrder.cook_id,
      time_submitted: timeSubmitted,
      status: this.selectedOrder.status,
      recipe: (this.selectedOrder.recipe || []).map(r => ({
        component_name: r.component_name,
        quantity: r.quantity ?? 0,
        loss_type: r.loss_type && r.loss_type !== '' ? r.loss_type : 'served',
        loss_qty: r.loss_qty ?? 0,
      })),
      deductions,
    };

    // 🚀 API call
    const response = await axios.post(`/order-items/update-or-create`, payload);

    if (!response.data.success) {
      Swal.fire('Warning', response.data.message || 'Something went wrong.', 'warning');
      return;
    }

    // ✅ Success
Swal.fire({
  icon: 'success',
  title: 'Updated!',
  text: 'Order item updated successfully',
  timer: 1800,
  showConfirmButton: false
});

const updatedDetail = response.data.data.order_detail;
const updatedOrderStatus = updatedDetail?.status;

// 🔄 Update local list
const index = this.orderItems.findIndex(
  item => item.order_detail_id === updatedDetail.id
);

if (index !== -1) {
  this.orderItems[index].status = updatedDetail.status;
  this.orderItems[index].cook_id = this.selectedOrder.cook_id;
  this.orderItems[index].time_submitted = timeSubmitted;
}

// 🔁 REFRESH / REMOVE
if (['served', 'walked'].includes(updatedOrderStatus)) {
  this.fetchItems();
}

if (updatedDetail.status !== 'serving') {
  this.orderItems = this.orderItems.filter(
    i => i.order_detail_id !== updatedDetail.id
  );
}

/* ✅ ADD THIS BLOCK HERE */
if (this.selectedOrder?.recipe) {
  this.selectedOrder.recipe.forEach(r => {
    r.loss_type = '';
    r.loss_qty = 0;
  });
}

/* THEN CLOSE MODAL */
const modal = bootstrap.Modal.getInstance(
  document.getElementById("updateModal")
);
if (modal) modal.hide();

  } catch (error) {
    console.error("❌ Update failed:", error.response || error);

    let message = 'Failed to update order item.';
    if (error.response?.data?.message) {
      message = error.response.data.message;
    }

    Swal.fire('Error', message, 'error');
  }

  },
  submitPush() {
  const modal = bootstrap.Modal.getInstance(document.getElementById("updateModal"));

  if (!this.selectedOrder.order_id) {
    // SweetAlert for validation
    Swal.fire({
      icon: 'warning',
      title: 'Oops!',
      text: 'Please select an Order No',
    });
    return;
  }

  axios.post('/kitchen/push-item', {
    order_detail_id: this.selectedOrder.order_detail_id,
    new_order_id: this.selectedOrder.new_order_id,
    status: 'served'
  })
  .then(res => {
    console.log('Push success');

    modal.hide();
    this.fetchItems(); // refresh list

    // SweetAlert success
    Swal.fire({
      icon: 'success',
      title: 'Order Pushed!',
      text: 'The order has been successfully updated.',
      timer: 1500,
      showConfirmButton: false
    });
  })
  .catch(err => {
    console.error(err);

    // SweetAlert error
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Something went wrong while pushing the order.'
    });
  });
},

  submitUpdateStatus() {
  if (this.modalMode === 'push') {
    this.submitPush();
  } else {
    this.submitUpdate();
  }
}
}
});
</script>

@endsection