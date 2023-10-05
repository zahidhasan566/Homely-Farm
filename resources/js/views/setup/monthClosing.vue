<template>
    <div>
        <div id="add-edit-dept" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <ValidationObserver v-slot="{ handleSubmit }">
                        <form class="form-horizontal" id="formProduction" @submit.prevent="handleSubmit(onSubmit)">
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-12 col-md-4">
                                        <ValidationProvider name="ExpenseDate" mode="eager"
                                                            v-slot="{ errors }">
                                            <div class="form-group">
                                                <label for="name">Expense Date <span class="error">*</span></label>
                                                <datepicker v-model="monthClosingDate" :dayStr="dayStr"
                                                            placeholder="YYYY-MM-DD" :firstDayOfWeek="0"/>
                                                <span class="error-message"> {{ errors[0] }}</span>
                                            </div>
                                            <div class="form-group">
                                                <submit-form v-if="buttonShow" :name="buttonText"/>
                                            </div>
                                        </ValidationProvider>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </ValidationObserver>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import {bus} from "../../app";
import {Common} from "../../mixins/common";
import {mapGetters} from "vuex";
import moment from "moment"
import Login from "../../views/auth/Login.vue";

export default {
    mixins: [Common],
    data() {
        return {
            title: '',
            UserId: '',
            buttonText: 'Month Closing',
            status: '',
            confirm: '',
            type: 'add',
            monthClosingDate:'',
            actionType: '',
            buttonShow: true,
            category: [],
            categoryType: '',
            updateCategoryCode:'',
            expenseDate:'',
            production_code:'',
            expenseCode:'',
            items: [],
            locations: [],
            expenseHead:[],
            expenseHeadVal:'',
            naration:'',
            rate:'',
            employee:[],
            employeeVal:'',
            production_date: '',
            reference: '',
            dayStr: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
            fields: [
                {
                    item: '',
                    itemCode: '',
                    location: {
                        'Active': 'Y',
                        'LocationCode': '',
                        'LocationName': ''
                    },
                    uom:'',
                    rate: 0,
                    quantity: 0,
                    itemValue: 0,
                    LocationCode:''
                }
            ],
            errors: [],

        }
    },
    computed: {},
    mounted() {
        this.production_date = moment().format('yyyy-MM-DD');
        $('#add-edit-dept').on('hidden.bs.modal', () => {
            this.$emit('changeStatus')
        });
    },
    destroyed() {
        bus.$off('add-edit-expense')
    },
    methods: {
        checkFieldValue() {
            this.errors = [];
            let instance = this;
            if(instance.monthClosingDate === ''|| instance.monthClosingDate === undefined ){
                this.errors = 'Value Required'
            }
        },
        onSubmit() {
            this.checkFieldValue();
            if (this.errors.length === 0) {
                this.$store.commit('submitButtonLoadingStatus', true);
                let url = '';
                var  submitUrl = '';
                submitUrl = 'expense/add-month-closing/';
                this.axiosPost(submitUrl, {

                    monthClosingDate: this.monthClosingDate,

                }, (response) => {
                    this.successNoti(response.message);
                    $("#add-edit-dept").modal("toggle");
                    bus.$emit('refresh-datatable');
                    this.$store.commit('submitButtonLoadingStatus', false);
                }, (error) => {
                    this.errorNoti(error);
                    this.$store.commit('submitButtonLoadingStatus', false);
                })
            }

        }
    }
}
</script>

<style src="vue-multiselect/dist/vue-multiselect.min.css">
.card-header {
    background: linear-gradient(269deg, rgb(0 0 0), #007bffb8) !important;
}
</style>
<style>
.datepicker .vue-input, .date-range-picker .vue-input, .timepicker .vue-input, .datetime-picker .vue-input {
    padding: 7px !important;
}
</style>
