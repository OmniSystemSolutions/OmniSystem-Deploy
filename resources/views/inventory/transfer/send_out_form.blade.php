@extends('layouts.app')
@section('content')
<style>
   button[disabled] {
   pointer-events: none;
   opacity: 0.6;
   cursor: not-allowed;
   }
</style>
<div class="main-content" id="app">
   <div>
      <div class="breadcrumb">
         <h1 class="mr-3">
            Invetory Transfer Send Out Form
         </h1>
         <ul>
            <li><a href="/accounts-receivable">Invetory</a></li>
         </ul>
      </div>
      <div class="separator-breadcrumb border-top"></div>
   </div>
   <div class="wrapper">
      <div class="card">
         <div class="card-body">
            <div class="row">
               <!-- Entry Date/Time -->
               <div class="col-sm-12 col-md-6 col-lg-4">
                  <fieldset class="form-group">
                     <legend tabindex="-1" class="bv-no-focus-ring col-form-label pt-0">Date and Time of Request</legend>
                     <div class="d-flex align-items-center">
                        <input type="datetime-local"
                           class="form-control"
                           v-model="form.requestedDatetime"
                           readonly/>
                     </div>
                  </fieldset>
               </div>
               <!-- Destination -->
               <div class="col-sm-12 col-md-6 col-lg-4">
                  <fieldset class="form-group">
                     <legend class="col-form-label pt-0">Destination</legend>
                     <input type="text" class="form-control" v-model="form.destinationName" readonly>
                  </fieldset>
               </div>
               <!-- Reference Number -->
               <div class="col-sm-12 col-md-6 col-lg-4">
                  <fieldset class="form-group">
                     <legend tabindex="-1" class="bv-no-focus-ring col-form-label pt-0">Reference #</legend>
                     <input type="text" class="form-control" v-model="form.referenceNo" readonly>
                  </fieldset>
               </div>
               <div class="col-sm-12 col-md-6 col-lg-4">
               </div>
               <!-- Delivery Personel -->
               <div class="col-sm-12 col-md-6 col-lg-4">
                  <fieldset class="form-group">
                     <legend tabindex="-1" class="bv-no-focus-ring col-form-label pt-0">Delivery Personel</legend>
                     <input type="text" class="form-control" v-model="deliveryPersonel">
                  </fieldset>
               </div>
               <!-- Delivery Number -->
               <div class="col-sm-12 col-md-6 col-lg-4">
                  <fieldset class="form-group">
                     <legend tabindex="-1" class="bv-no-focus-ring col-form-label pt-0">DR #</legend>
                     <input type="text" class="form-control" v-model="deliveryNo">
                  </fieldset>
               </div>
               <!-- List of Tables Tabe -->
               <div class="col-sm-12">
                  <div class="list-group mt-2">
                     <div class="list-group-item d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 font-weight-bold">List of Items to Send</h6>
                     </div>
                     <div class="list-group-item">
                        <table class="table-hover tableOne vgt-table">
                           <thead>
                              <tr>
                                 <th>Name</th>
                                 <th>SKU(Product Code)</th>
                                 <th>Category</th>
                                 <th>Quantity On Hand</th>
                                 <th>Unit</th>
                                 <th>Quantity Requested</th>
                                 <th>Quantity Sent</th>
                                 <th>Quantity to Send</th>
                              </tr>
                           </thead>
                           <tbody>
                              <tr v-for="item in items" :key="item.id">
                                 <td>@{{ item.name }}</td>
                                 <td>@{{ item.code }}</td>
                                 <td>@{{ item.category ? item.category.name : 'N/A' }}</td>
                                 <td>@{{ item.onhand }}</td>
                                 <td>@{{ item.unit }}</td>
                                 <td>@{{ item.requested_quantity }}</td>
                                 <td>@{{ item.sent_quantity }}</td>
                                 <td>
                                    <div style="width: 200px;">
                                       <div role="group" class="input-group input-group-sm">
                                          <div class="input-group-prepend">
                                             <button
                                                type="button"
                                                class="btn btn-primary"
                                                :class="{ disabled: quantities[item._type][item.id] <= 0 }"
                                                @click="decrementQuantity(item)"
                                                :disabled="quantities[item._type][item.id] <= 0"
                                             >-</button>
                                          </div>
                                          {{-- <input
                                             :id="'qty-' + item.id"
                                             type="number"
                                             class="form-control"
                                             :value="quantities[item._type][item.id] ?? 0"
                                             min="0"
                                             :max="item.requested_quantity - item.sent_quantity"
                                             step="0.01"
                                             @input="onQuantityInput($event, item)"
                                             @keydown="blockInvalidKeys"
                                             /> --}}
                                             <input
    :id="'qty-' + item.id"
    type="number"
    class="form-control"
    :value="quantities[item._type][item.id] ?? ''"
    min="0"
    :max="(item.requested_quantity - item.sent_quantity).toFixed(2)"
    step="0.01"
    @input="onQuantityInput($event, item)"
    @keydown="blockInvalidKeys"
/>

                                          <div class="input-group-append">
                                             <button type="button"
                                                class="btn btn-primary"
                                                :class="{ disabled: quantities[item._type][item.id] >= getMaxQuantity(item) }"
                                                @click="incrementQuantity(item)"
                                                :disabled="quantities[item._type][item.id] >= getMaxQuantity(item)">+</button>
                                          </div>
                                       </div>
                                    </div>
                                 </td>
                              </tr>
                              <tr v-if="!items.length">
                                 <td colspan="7" class="text-center text-muted">No items found</td>
                              </tr>
                           </tbody>
                        </table>
                     </div>
                  </div>
               </div>
               <!-- Buttons -->
               <div class="mt-3 col-md-12">
                  <div class="d-flex mt-4">
                     <button type="button" class="btn btn-primary mr-2" @click="submitForm">
                     <i class="i-Yes me-2 font-weight-bold"></i> Submit
                     </button>
                     <button type="button" class="btn btn-outline-secondary" @click="goBack">
    Cancel
</button>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<script>
   new Vue({
      el: '#app',
   
      data() {
         return {
               transfer: @json($transfer ?? null),
               form: {
                  requestedDatetime: @json($transfer->requested_datetime),
                  destinationName: '',
                  referenceNo: @json($transfer->reference_no),
               },
               destination: {
                  name: '',
               },
               referenceNo: '',
               typeOptions: [
                  { label: 'Full Transfer', value: 'full' },
                  { label: 'Partial Transfer', value: 'partial' },
               ],
               deliveryPersonel: '',
               deliveryNo: @json($delivery_no ?? ''),
               items: [],
               quantities: {
                  product: {},
                  component: {},
               },
            };
         },
         methods: {
            formatUtcToDatetimeLocal(utcString) {
               const date = new Date(utcString);
               const year = date.getFullYear();
               const month = String(date.getMonth() + 1).padStart(2, '0');
               const day = String(date.getDate()).padStart(2, '0');
               const hours = String(date.getHours()).padStart(2, '0');
               const minutes = String(date.getMinutes()).padStart(2, '0');
               return `${year}-${month}-${day}T${hours}:${minutes}`;
            },
//             onQuantityInput(event, item) {
//     let value = event.target.value;

//     // Block scientific notation
//     value = value.replace(/[eE+\-]/g, '');
//     value = Number(value);

//     if (!Number.isFinite(value)) value = 0;

//     // Clamp to remaining quantity (requested - sent)
//     const remaining = (item.requested_quantity || 0) - (item.sent_quantity || 0);
//     if (value > remaining) value = remaining;
//     if (value < 0) value = 0;

//     // Update Vue state
//     this.$set(this.quantities[item._type], item.id, value);

//     // Force input DOM
//     event.target.value = value;
// },
onQuantityInput(event, item) {
    let value = parseFloat(event.target.value);

    if (isNaN(value) || value < 0) {
        value = 0;
    }

    // Clamp to max remaining
    const remaining = parseFloat(item.requested_quantity) - parseFloat(item.sent_quantity);
    if (value > remaining) {
        value = remaining;

         Swal.fire({
                icon: 'warning',
                title: 'Quantity Exceeded',
                text: `You cannot exceed the remaining quantity of ${remaining.toFixed(2)}.`,
            });
    }

    // Round to 2 decimals
    value = Math.round(value * 100) / 100;

    // Save in reactive object
    this.$set(this.quantities[item._type], item.id, value);

   //  event.target.value = value.toFixed(2);
},


            blockInvalidKeys(e) {
               // Prevent e, E, +, -
               if (['e', 'E', '+', '-'].includes(e.key)) {
                  e.preventDefault();
               }
            },
            getMaxQuantity(item) {
    const requested = Number(item.requested_quantity) || 0;
    const sent = Number(item.sent_quantity) || 0;
    const onhand = Number(item.onhand) || 0;

    return Math.max(
        0,
        Math.min(requested - sent, onhand)
    );
},
            incrementQuantity(item) {
    const type = item._type;
    const max = Math.min(Number(item.requested_quantity), Number(item.onhand));
   
    let value = this.quantities[type][item.id] ?? 0;
    if (value >= max) return;
    value = +(value + 1).toFixed(2);
    this.$set(this.quantities[type], item.id, value);
   
    // force update input DOM
    this.$nextTick(() => {
        const input = document.querySelector(`#qty-${item.id}`);
        const val = input.value;
        if (input) input.value = value;
        console.log('Updated input value:', val);
    });
   },
   
   decrementQuantity(item) {
      const type = item._type;
      const id = item.id;
   
      let current = this.quantities[type][id] ?? 0;
   
      current -= 1;
   
      // Prevent negative values
      if (current < 0) {
         current = 0;
      }
   
      this.$set(this.quantities[type], id, current);
   },
   goBack() {
        const lastTab = localStorage.getItem('inventory_transfer_last_tab');

        let url = '/inventory/transfer';

        if (lastTab) {
            url += `?status=${lastTab}`;
        }

        window.location.href = url;
    },
   submitForm() {
      // // 1️⃣ Validate quantities
      // const invalidItems = this.items.filter(item => {
      //    const qty = this.quantities[item._type][item.id] ?? 0;
      //    return qty <= 0;
      // });
   
      // if (invalidItems.length) {
      //    Swal.fire({
      //       icon: 'warning',
      //       title: 'Invalid Quantities',
      //       text: 'Please enter a quantity greater than zero for all items.',
      //    });
      //    return;
      // }
      const hasAtLeastOnePositiveQty = this.items.some(item => {
   const qty = Number(this.quantities[item._type][item.id] ?? 0);
   return qty > 0;
});

if (!hasAtLeastOnePositiveQty) {
   Swal.fire({
      icon: 'warning',
      title: 'Invalid Quantities',
      text: 'At least one item must have a quantity greater than zero.',
   });
   return;
}

      if (!this.deliveryPersonel.trim()) {
         Swal.fire({
            icon: 'warning',
            title: 'Missing Delivery Personel',
            text: 'Please enter the name of the delivery personel.',
         });
         return;
      }
   
      // 2️⃣ Prepare items_onload payload
      const itemsOnload = this.items.map(item => ({
         inventory_transfer_item_id: item.id,
         type: item._type,
         quantity: this.quantities[item._type][item.id],
      }));
   
      // 3️⃣ Prepare form payload
      const payload = {
         inventory_transfer_id: this.transfer.id,
         delivery_request_no: this.deliveryNo,
         personel_name: this.deliveryPersonel,
         items_onload: itemsOnload,
      };
   
      // 4️⃣ Submit via Axios
      axios.post(`/inventory/transfer/${this.transfer.id}/send`, payload)
         .then(response => {
            Swal.fire({
               icon: 'success',
               title: 'Delivery saved!',
               text: 'Items have been successfully recorded.',
            }).then(() => {
               // Optional: redirect to index
               window.location.href = '/inventory/transfer';
            });
         })
         .catch(error => {
            console.error(error);
            Swal.fire({
               icon: 'error',
               title: 'Error',
               text: 'Something went wrong while saving. Please try again.',
            });
         });
   },
   },
         mounted() {
            if (this.transfer) {
               this.form.requestedDatetime = this.formatUtcToDatetimeLocal(this.transfer.requested_datetime);
               this.form.referenceNo = this.transfer.reference_no;
               this.form.destinationName =
                  this.transfer.destination_branch?.name ?? '';
               this.items = (this.transfer.items || []).map(item => {
                  const isProduct = !!item.product_id;
                  const source     = isProduct ? item.product : item.component;
                  const branchStock = source?.branch_stock_for_current;

                  return {
                     id: item.id,
                     _type: isProduct ? 'product' : 'component',

                     name:     source?.name ?? 'N/A',
                     code:     source?.code  ?? 'N/A',
                     category: source?.category ?? null,
                     unit:     source?.unit?.name ?? 'N/A',
                     onhand:   isProduct
                                 ? (branchStock?.quantity ?? 0)
                                 : (branchStock?.onhand   ?? 0),

                     requested_quantity: item.quantity_requested,
                     sent_quantity:      item.quantity_sent,
                  };
               });
               this.items.forEach(item => {
                  if (!this.quantities[item._type][item.id]) {
                     this.$set(this.quantities[item._type], item.id, 0);
                  }
               });
            }
            console.log(this.items);
         },
      });
</script>
@endsection