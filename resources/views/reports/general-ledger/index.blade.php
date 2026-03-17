@extends('layouts.app')
@section('content')
<style>
     .dropdown-menu {
        position: relative;
    }
    .range-picker{
    border:1px solid #ddd;
    padding:15px;
    width:350px;
    border-radius:6px;
    background:#fff;
}

.range-buttons{
    margin-bottom:10px;
}

.range-buttons button{
    padding:6px 10px;
    margin-right:5px;
    border:1px solid #ccc;
    background:#f8f9fa;
    cursor:pointer;
    border-radius:4px;
    transition:0.2s;
}

/* HOVER */
.range-buttons button:hover{
    background:#ff630f;
    color:#fff;
    border-color:#ff630f;
}

/* SELECTED */
.range-buttons button.active{
    background:#ff630f !important;
    color:#fff !important;
    border-color:#ff630f !important;
}

.range-inputs{
    display:flex;
    align-items:center;
    gap:8px;
}

.range-inputs input{
    padding:5px;
}

.range-result{
    margin-top:10px;
    font-size:14px;
}
.range-buttons button:focus{
    outline:none;
}
</style>
<div class="main-content" id="app">
   <div>
      <div class="breadcrumb">
         <h1 class="mr-3">
            General Ledger
         </h1>
         <ul>
            <li><a href="#">Reports</a></li>
         </ul>
      </div>
      <div class="separator-breadcrumb border-top"></div>
   </div>
   <div class="wrapper">
      <div class="card wrapper">
         <div class="card-body">
            <div class="column">
               <div class="col-sm-12 col-md-4">
                  <fieldset class="form-group">
                     <legend class="col-form-label pt-0">Select Cash or Cash Equivalents</legend>
                     <v-select
                        v-model="selectedCashEquivalent"
                        placeholder="Select Cash Equivalent"
                        :options="cashEquivalents"
                        :clearable="false"
                        label="account_number"
                        >
                     </v-select>
                  </fieldset>
               </div>
             <div class="range-picker" style="margin-left: 12px">

               <div class="range-buttons">
                  <button 
                     :class="{ active: selectedRange === 30 }"
                     @click.stop="setRange(30)">
                     Last 30 Days
                  </button>

                  <button 
                     :class="{ active: selectedRange === 60 }"
                     @click.stop="setRange(60)">
                     Last 60 Days
                  </button>

                  <button 
                     :class="{ active: selectedRange === 90 }"
                     @click.stop="setRange(90)">
                     Last 90 Days
                  </button>
               </div>

               <div class="range-inputs">
                     <input type="date" v-model="startDate">
                     <span>to</span>
                     <input type="date" v-model="endDate">
               </div>

               <div class="range-result">
                     <strong>Selected:</strong>
                     @{{ startDate }} → @{{ endDate }}
               </div>

            </div>
            </div>
         </div>
      </div>
      <div class="card wrapper">
         <div class="card-body">
            <div class="vgt-wrap">
               <div class="vgt-inner-wrap">
                  <div class="vgt-global-search vgt-clearfix">
                  </div>
                  <div class="vgt-fixed-header">
                  <!---->
                  </div>
                  <div class="vgt-responsive mt-3">
                     <table id="vgt-table" class="table-hover tableOne vgt-table ">
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
                        </colgroup>
                        <thead>
                           <tr>
                              <th>Date and Time</th>
                              <th>Transaction</th>
                              <th>Reference #</th>
                              <th>Type</th>
                              <th>Description</th>
                              <th>Payor</th>
                              <th>Payee</th>
                              <th>Method of Payments</th>
                              <th>Debit</th>
                              <th>Credit</th>
                              <th>Balance</th>
                           </tr>
                        </thead>
                        <tbody>
                           <tr v-for="row in displayLedger" :key="row.id">
                              <td class="text-left">@{{ formatDateTime(row.date) }}</td>
                              <td class="text-left">@{{ row.transaction ?? '-' }}</td>
                              <td class="text-left">@{{ row.reference_number ?? '-' }}</td>
                              <td class="text-left">@{{ row.type ?? '-' }}</td>
                              <td class="text-left">@{{ row.description ?? '-' }}</td>
                              <td class="text-left">@{{ row.payor ?? '-' }}</td>
                              <td class="text-left">@{{ row.payee ?? '-' }}</td>
                              <td class="text-left">@{{ row.payment_method ?? '-' }}</td>
                              <td class="text-left">@{{ formatAmount(row.debit) }}</td>
                              <td class="text-left">@{{ formatAmount(row.credit) }}</td>
                              <td class="text-left">@{{ formatAmount(row.running_balance) }}</td>
                           </tr>

                           <tr v-if="computedLedger.length === 0">
                              <td colspan="11" style="text-align:center;">No data found</td>
                           </tr>
                        </tbody>
                     </table>
                  </div>
                  <div class="vgt-wrap__footer vgt-clearfix">
                     <div class="footer__row-count vgt-pull-left">
                        <form>
                           <label for="rows" class="footer__row-count__label">Rows per page:</label>
                           <select id="rows" name="perPageSelect" class="footer__row-count__select">
                              <option value="10">10</option>
                              <option value="20">20</option>
                              <option value="30">30</option>
                              <option value="40">40</option>
                              <option value="50">50</option>
                              <option value="-1">All</option>
                           </select>
                        </form>
                     </div>
                     <div class="footer__navigation vgt-pull-right">
                        {{-- Pagination disabled (collection returned). Enable by paginating in controller) --}}
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
@endsection
@section('scripts')
<script>
Vue.component('v-select', VueSelect.VueSelect);
new Vue({
   el: '#app',
   data() {
      return {
         cashEquivalents: @json($cashEquivalents),
         selectedCashEquivalent: '',
         startDate: '',
         endDate: '',
         selectedRange: 30,
         ledgerData: []
      }
      },
      mounted() {
         this.setRange(30)
         console.log(@json($cashEquivalents))
      },
      methods: {
         setRange(days) {
           this.selectedRange = days;

            const end = new Date();
            const start = new Date();

            start.setDate(end.getDate() - (days - 1));

            this.startDate = start.toISOString().split('T')[0];
            this.endDate = end.toISOString().split('T')[0];

            console.log('Range set:', this.startDate, 'to', this.endDate);

            this.fetchLedger();
                  },

                  formatDate(date) {
                     const y = date.getFullYear()
                     const m = String(date.getMonth() + 1).padStart(2, '0')
                     const d = String(date.getDate()).padStart(2, '0')
                     return `${y}-${m}-${d}`
            },
            formatAmount(value) {
               return Number(value || 0).toLocaleString(undefined, {
                     minimumFractionDigits: 2,
                     maximumFractionDigits: 2
               });
            },

            formatDateTime(datetime) {
               if (!datetime) return '-';

               const date = new Date(datetime);

               return date.toLocaleString(); // you can customize format
            },
            fetchLedger() {
               console.log('START DATE:', this.startDate);
               console.log('END DATE:', this.endDate);
               console.log('SELECTED CASH:', this.selectedCashEquivalent);

               axios.get('/reports/general-ledger/fetch', {
                  params: {
                        start_date: this.startDate,
                        end_date: this.endDate,
                        cash_equivalent_id: this.selectedCashEquivalent?.id
                  }
               })
               .then(response => {
                  console.log('RESPONSE:', response.data); // ✅ THIS is what you want
                  this.ledgerData = response.data;
               })
               .catch(error => {
                  console.error('ERROR:', error);
               });
            }
      },
      computed: {
         computedLedger() {
            let balance = 0;

            return this.ledgerData.map(row => {
                  const debit = Number(row.debit ?? 0);
                  const credit = Number(row.credit ?? 0);

                  balance += debit - credit;

                  return {
                     ...row,
                     running_balance: balance
                  };
            });
         },
         displayLedger() {
            return [...this.computedLedger].reverse();
         }
      },
      watch: {
         startDate() {
            this.fetchLedger();
         },
         endDate() {
            this.fetchLedger();
         },
         selectedCashEquivalent() {
            this.fetchLedger();
         }
      }     
   });
</script>
@endsection
