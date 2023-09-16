<template>
    <div>
        <div class="modal fade" id="add-edit-dept" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="modal-title modal-title-font" id="exampleModalLabel">{{ title }}</div>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" @click="closeModal">
                            Close
                        </button>
                    </div>
                    <ValidationObserver v-slot="{ handleSubmit }">
                        <form class="form-horizontal" id="formProduction" @submit.prevent="handleSubmit(onSubmit)">
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-12 col-md-4">
                                        <ValidationProvider name="ExpenseDate" mode="eager"
                                                            v-slot="{ errors }">
                                            <div class="form-group">
                                                <label for="name">Expense Date <span class="error">*</span></label>
                                                <datepicker v-model="expenseDate" :dayStr="dayStr"
                                                            placeholder="YYYY-MM-DD" :firstDayOfWeek="0"/>
                                                <span class="error-message"> {{ errors[0] }}</span>
                                            </div>
                                        </ValidationProvider>
                                    </div>
                                    <div class="col-12 col-md-4">
                                            <div class="form-group">
                                                <label for="ExpenseHead">Expense Head </label>
                                                <multiselect v-model="expenseHeadVal" :options="expenseHead"
                                                             :multiple="false"
                                                             @input="getItemByCategory"
                                                             :close-on-select="true"
                                                             :clear-on-select="false" :preserve-search="true"
                                                             placeholder="Select Expense Head"
                                                             label="ExpenseHead" track-by="HeadCode">

                                                </multiselect>
                                            </div>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <ValidationProvider name="Category" mode="eager" rules="required"
                                                            v-slot="{ errors }">
                                            <div class="form-group">
                                                <label for="Category">Category <span class="error">*</span></label>
                                                <multiselect v-model="categoryType" :options="category" v-if="actionType==='add'"
                                                             :multiple="false"
                                                             @input="getItemByCategory"
                                                             :close-on-select="true"
                                                             :clear-on-select="false" :preserve-search="true"
                                                             placeholder="Select Category"
                                                             label="CategoryName" track-by="CategoryCode">

                                                </multiselect>
                                                <multiselect v-model="categoryType" :options="category" v-else
                                                             :multiple="false"
                                                             :disabled="true"
                                                             :close-on-select="true"
                                                             :clear-on-select="false" :preserve-search="false"
                                                             placeholder="Select Category"
                                                             label="CategoryName" track-by="CategoryCode">

                                                </multiselect>
                                                <span class="error-message"> {{ errors[0] }}</span>
                                            </div>
                                        </ValidationProvider>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <label for="naration">Naration </label>
                                        <input  type="text"  class="form-control"
                                               v-model="naration" placeholder="Naration">
                                    </div>
                                    <!-- <div class="col-12 col-md-4">
                                        <label for="Rate">Rate</label>
                                        <input  type="text"  class="form-control"
                                               v-model="rate" placeholder="Rate">
                                    </div> -->

                                </div>
                            </div>
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3" style="margin-bottom: 10px;">
                                            <label for="payment-required-by">Add Expense</label>
                                        </div>
<!--                                        <div class="offset-md-5 col-md-4">-->
<!--                                            <button style="float: right;" id="add-row" type="button"-->
<!--                                                    class="btn btn-success btn-sm" @click="addRow">Add Row-->
<!--                                            </button>-->
<!--                                        </div>-->
                                    </div>
                                    <div class="table-responsive">
                                        <table
                                            class="table  table-bordered table-striped   nowrap dataTable no-footer dtr-inline table-sm">
                                            <thead class="thead-dark">
                                            <tr>
                                                <th>Item </th>
                                                <th>Item Code</th>
                                                <th>Pac Size</th>
                                                <th>Location</th>
                                                <th>Rate</th>
                                                <th>Quantity</th>
                                                <th>Amount</th>
                                                <!-- <th>Action</th> -->
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr v-for="(field,index) in fields" :key="index">
                                                <td>
                                                    <select class="form-control" :class="{'error-border': errors[0]}"
                                                            @change="setItemCode($event,index)"
                                                            v-model="field.itemCode" name="item">
                                                        <option v-for="(item,index) in items"
                                                                :key="index"
                                                                :value="item.ItemCode">
                                                            {{ item.ItemName }}
                                                        </option>
                                                    </select>

                                                    <span class="error"
                                                          v-if="errors[index] !== undefined && errors[index].item !== undefined">{{
                                                            errors[index].item
                                                        }}</span>

                                                </td>
                                                <td>
                                                    <input readonly type="text"  class="form-control"
                                                           v-model="field.itemCode" placeholder="itemCode">

                                                </td>
                                                <td>
                                                    <input readonly type="text"  class="form-control"
                                                           v-model="field.uom" placeholder="Pack Size" min="0">

                                                </td>
                                                <td>
                                                    <select class="form-control" :class="{'error-border': errors[0]}"
                                                            v-model="field.LocationCode" name="item">
                                                        <option value="">SELECT</option>
                                                        <option v-for="(item,index) in locations"
                                                                :key="index"
                                                                :value="item.LocationCode">
                                                            {{ item.LocationName }}
                                                        </option>
                                                    </select>
                                                    <span class="error"
                                                          v-if="errors[index] !== undefined && errors[index].location !== undefined">{{
                                                            errors[index].location
                                                        }}</span>
                                                </td>
                                                <td>
                                                    <input  type="text"  class="form-control"
                                                            v-model="field.rate" placeholder="Rate"
                                                            @keyup="calculateAmount">
                                                </td>
                                                <td>
                                                    <input type="text"  class="form-control" style="text-align: end"
                                                           v-model="field.quantity" placeholder="quantity"
                                                           @keyup="calculateAmount">
                                                    <span class="error"
                                                          v-if="errors[index] !== undefined && errors[index].quantity !== undefined">{{
                                                            errors[index].quantity
                                                        }}</span>

                                                </td>
                                                <td>
                                                    <input type="text" class="form-control" style="text-align: end"
                                                           v-model="field.itemValue"
                                                           placeholder="Value"
                                                           readonly="">
                                                </td>
                                                <!-- <td>
                                                    <button type="button" class="btn btn-danger btn-sm"
                                                            @click="removeRow(index)"><i
                                                        class="ti-close"></i></button>
                                                </td> -->
                                            </tr>
                                            </tbody>
                                        </table>

                                        <div class="col-md-12" style="text-align: end;margin-top:10px">
                                            <submit-form v-if="buttonShow" :name="buttonText"/>
                                        </div>

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

export default {
    mixins: [Common],
    data() {
        return {
            title: '',
            UserId: '',
            buttonText: '',
            status: '',
            confirm: '',
            type: 'add',
            actionType: '',
            buttonShow: false,
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
        bus.$on('add-edit-expense', (row) => {
            if (row) {
                let instance = this;
                this.axiosGet('expense/get-expense-info/' + row.ExpenseCode, function (response) {
                    instance.title = 'Update expense';
                    instance.buttonText = "Update";
                    instance.buttonShow = true;
                    instance.actionType = 'edit';
                    instance.fields.splice(0, 1)
                    instance.getData();
                    var expenseInfo = response.expenseInfo;
                    console.log(expenseInfo)

                    //Master
                    instance.expenseDate = expenseInfo[0].ExpenseDate;
                    instance.expenseCode = expenseInfo[0].ExpenseCode;
                    instance.expenseHeadVal =[{
                        'HeadCode':expenseInfo[0].HeadCode,
                        'ExpenseHead':expenseInfo[0].ExpenseHead
                    }];
                    instance.updateCategoryCode = expenseInfo[0].CategoryCode
                    instance.naration = expenseInfo[0].Naration
                    //instance.rate = expenseInfo[0].Rate

                    instance.categoryType=[{
                        'CategoryCode': expenseInfo[0].CategoryCode,
                        'CategoryName': expenseInfo[0].CategoryName
                    }
                    ]

                    //Details
                    expenseInfo.forEach(function (item,index) {
                        instance.fields.push({
                            item: {
                                'ItemName': item.ItemName,
                                'ItemCode': item.ItemCode,
                            },
                            itemCode: item.ItemCode,
                            // location: {
                            //     'Active': 'Y',
                            //     'LocationCode': item.LocationCode,
                            //     'LocationName': item.LocationName
                            // },
                            rate: item.Rate,
                            quantity: item.Quantity,
                            itemValue: item.Amount,
                            LocationCode: item.LocationCode,
                            uom: item.UOM
                        })
                    });
                    instance.getItemByCategory();
                }, function (error) {

                });
            } else {
                this.title = 'Add expense';
                this.buttonText = "Add";
                this.UserId = '';

                this.status = '';
                this.buttonShow = true;
                this.actionType = 'add'
                this.getData();
            }
            $("#add-edit-dept").modal("toggle");
        })
    },
    destroyed() {
        bus.$off('add-edit-expense')
    },
    methods: {
        closeModal() {
            $("#add-edit-dept").modal("toggle");
        },
        calculateAmount(){
            this.fields[0].itemValue = parseFloat(parseFloat(this.fields[0].rate) * parseFloat(this.fields[0].quantity)).toFixed(2);
        },
        addRow() {
            this.fields.push({
                item: '',
                itemCode: '',
                location: {
                    'Active': 'Y',
                    'LocationCode': '',
                    'LocationName': ''
                },
                rate: 0,
                quantity: 0,
                itemValue: 0,
                LocationCode:'',
                uom:''
            });
        },
        removeRow(id) {
            this.fields.splice(id, 1)
            if (this.errors[id] !== undefined) {
                this.errors.splice(id, 1)
            }
        },
        getData() {
            let instance = this;
            this.axiosGet('expense/supporting-data', function (response) {
                instance.category = response.category;
                instance.expenseHead = response.expenseHead;
                instance.employee = response.employee;
            }, function (error) {
            });
        },
        getItemByCategory() {
            let instance = this;
            let tableField = instance.fields;
            if (instance.actionType==='add'){
                tableField.forEach(function (item,index) {
                    tableField.splice(index, 1)
                });
                this.addRow();
            }
            let categoryCode = '';
            if(instance.actionType==='add'){
                categoryCode = instance.categoryType.CategoryCode;
            }
            else{
                categoryCode = instance.updateCategoryCode;
            }
            let url = 'expense/category-wise-item';
            this.axiosPost(url, {
                CategoryCode:categoryCode ,
            }, (response) => {
                instance.items = response.items;
                instance.locations = response.locations;
            }, (error) => {
                this.errorNoti(error);

            })
        },
        setItemCode(e, key) {
            let item = this.items.find((row) => {
                return row.ItemCode === e.target.value
            })
            var itemCode = e.target.value;
            let instance = this;
            instance.fields[key].itemCode = e.target.value;
            instance.fields[key].uom = item ? item.UOM : '';
        },
        checkFieldValue() {
            this.errors = [];
            let instance = this;
            if(instance.categoryType === ''|| instance.categoryType === undefined || instance.expenseDate === ''|| instance.expenseDate === undefined ){
                this.errors = 'Value Required'
            }

        },
        onSubmit() {
            this.checkFieldValue();
            if (this.errors.length === 0) {
                this.$store.commit('submitButtonLoadingStatus', true);
                let url = '';
                var  submitUrl = '';
                if (this.actionType === 'add') {
                    submitUrl = 'expense/add';
                }
                if(this.actionType === 'edit' ){
                    submitUrl = 'expense/update';
                }
                this.axiosPost(submitUrl, {
                    expenseCode: this.expenseCode,
                    expenseDate: this.expenseDate,
                    expenseHeadVal: this.expenseHeadVal,
                    categoryType: this.categoryType,
                    narration : this.naration,
                    //rate: this.rate,
                    details: this.fields,
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
