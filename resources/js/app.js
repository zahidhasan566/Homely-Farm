require('./bootstrap');
require('./validation/index');

import Vue from 'vue'
import App from './views/App'
import router from './router/index';
import store from './store/index'
import {baseurl} from './base_url'

// main origin
Vue.prototype.mainOrigin = baseurl


//Multi select
//Vue Multiselect
import Multiselect from 'vue-multiselect'
Vue.component('multiselect', Multiselect)
//Bus to transfer data
export const bus = new Vue();

//Toaster
import Toaster from 'v-toaster'
import 'v-toaster/dist/v-toaster.css'
Vue.use(Toaster, {timeout: 3000})

//moment
import moment from 'moment'


//Component
Vue.component('skeleton-loader', require('./components/loaders/Straight').default);
Vue.component('submit-form', require('./components/buttons/Submit').default);
Vue.component('advanced-datatable', require('./components/datatable/Advanced').default);
Vue.component('breadcrumb', require('./components/layouts/Breadcrumb').default);
Vue.component('data-export', require('./components/datatable/Export').default);
Vue.component('add-edit-user',require('./components/users/AddEditModal').default);
Vue.component('reset-password',require('./components/users/Editpassword').default);
Vue.component('submit-form', require('./components/buttons/Submit').default);

//production Component
Vue.component('add-edit-production',require('./components/production/AddEditModal').default);
Vue.component('add-edit-purchase',require('./components/purchase/AddEditModal').default);
Vue.component('add-edit-sales',require('./components/sales/AddEditModal').default);
Vue.component('add-edit-customers',require('./components/customers/AddEditModal').default);

Vue.component('add-edit-category',require('./components/setup/CategoryAddEditModal.vue').default);
Vue.component('add-edit-vaccineschedule',require('./components/Vaccineschedule/VaccineScheduleAddEditModal.vue').default);
Vue.component('add-edit-location',require('./components/setup/LocationAddEditModal.vue').default);
Vue.component('add-edit-category-location',require('./components/setup/CategoryLocationAddEditModal.vue').default);
Vue.component('add-edit-expense-head',require('./components/setup/ExpenseHeadAddEditModal.vue').default);
Vue.component('add-edit-expense',require('./components/Expense/AddEditModal.vue').default);
Vue.component('add-edit-item',require('./components/setup/itemAddEditModal.vue').default);
//Vue.component('add-edit-vaccine-schedule',require('./components/Vaccineschedule/VaccineScheduleAddEditModal.vue').default);
//Vue.component('add-edit-vaccineschedule',require('./components/setup/CategoryAddEditModal.vue').default);

Vue.component('add-edit-transfer',require('./components/transfer/AddEditModal').default);
Vue.component('add-edit-medicine-transfer',require('./components/transfer/MedicineAddEditModal.vue').default);

//Vue Datepicker
import { Datepicker } from '@livelybone/vue-datepicker';
Vue.component('datepicker', Datepicker);
import '@livelybone/vue-datepicker/lib/css/index.css'



const app = new Vue({
    el: '#app',
    store: store,
    components: {App},
    router,
});
