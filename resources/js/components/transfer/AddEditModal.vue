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
                                    <div class="col-12 col-md-6">
                                        <ValidationProvider name="transfer_date" mode="eager"
                                                            v-slot="{ errors }">
                                            <div class="form-group">
                                                <label for="name">Transfer Date <span class="error">*</span></label>
                                                <datepicker v-model="transfer_date" :dayStr="dayStr"
                                                            placeholder="YYYY-MM-DD" :firstDayOfWeek="0"/>
                                                <span class="error-message"> {{ errors[0] }}</span>
                                            </div>
                                        </ValidationProvider>
                                    </div>
                                    <div class="col-12 col-md-6">
                                            <div class="form-group">
                                                <label for="name">Reference</label>
                                                <input type="text" class="form-control"
                                                        id="Reference"
                                                       v-model="reference" name="staff-name" placeholder="Reference">
                                            </div>
                                    </div>
                                    <div class="col-12 col-md-6">
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
                                    <div class="col-12 col-md-4" v-if="actionType==='edit'">
                                        <div class="form-group">
                                            <label for="user-type">Return</label>
                                            <br>
                                            <input type="checkbox" value="Y" id="return"> Return
                                        </div>


                                    </div>

                                </div>
                            </div>
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3" style="margin-bottom: 10px;">
                                            <label for="payment-required-by">Add Transfer</label>
                                        </div>
                                        <div class="offset-md-5 col-md-4">
                                            <button style="float: right;" id="add-row" type="button"
                                                    class="btn btn-success btn-sm" @click="addRow">Add Row
                                            </button>
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table
                                            class="table  table-bordered table-striped   nowrap dataTable no-footer dtr-inline table-sm">
                                            <thead class="thead-dark">
                                            <tr>
                                                <th>Item <span class="required-field">*</span></th>
                                                <th>Item Code<span class="required-field">*</span></th>
                                                <th>Pac Size<span class="required-field">*</span></th>
                                                <th>Transfer From<span class="required-field">*</span></th>
                                                <th>Transfer To<span class="required-field">*</span></th>
                                                <th>Quantity<span class="required-field">*</span></th>
                                                <th>Stock<span class="required-field">*</span></th>
                                                <th>Value<span class="required-field">*</span></th>
                                                <th>Total<span class="required-field">*</span></th>
                                                <th>Action</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr v-for="(field,index) in fields" :key="index">
                                                <td>
                                                    <select class="form-control" :class="{'error-border': errors[0]}"
                                                            @change="setItemCode($event,index)"
                                                            v-model="field.itemCode" name="item">
                                                        <option v-for="(item,i) in items"
                                                                :key="i"
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
                                                           v-model="field.itemCode" placeholder="itemCode" min="0">

                                                </td>
                                                <td>
                                                    <input readonly type="text"  class="form-control"
                                                           v-model="field.uom" placeholder="Pack Size" min="0">

                                                </td>
                                                <td>
                                                    <select class="form-control" :class="{'error-border': errors[0]}"
                                                            @change="checkItemStock($event,field.itemCode,index)"
                                                            v-model="field.LocationFromCode" name="locationfrom">
                                                        <option v-for="(item,i) in locations"
                                                                :key="i"
                                                                :value="item.LocationCode">
                                                            {{ item.LocationName }}
                                                        </option>
                                                    </select>
                                                    <span class="error"
                                                          v-if="errors[index] !== undefined && errors[index].locationfrom !== undefined">{{
                                                            errors[index].locationfrom
                                                        }}</span>
                                                </td>
                                                <td>
                                                    <select class="form-control" :class="{'error-border': errors[0]}"
                                                            v-model="field.LocationToCode" name="locationTo">
                                                        <option v-for="(item,i) in locations"
                                                                :key="i"
                                                                :value="item.LocationCode">
                                                            {{ item.LocationName }}
                                                        </option>
                                                    </select>
                                                    <span class="error"
                                                          v-if="errors[index] !== undefined && errors[index].locationTo !== undefined">{{
                                                            errors[index].locationTo
                                                        }}</span>
                                                </td>
                                                <td>
                                                    <input type="number" @input="calculateTotal(field, index)" class="form-control" style="text-align: end"
                                                           v-model="field.quantity" placeholder="quantity">
                                                    <span class="error"
                                                          v-if="errors[index] !== undefined && errors[index].quantity !== undefined">{{
                                                            errors[index].quantity
                                                        }}</span>

                                                </td>
                                                <td style="text-align: end">
                                                    <input readonly type="text" class="form-control" style="text-align: end"
                                                           v-model="field.itemStock" placeholder="Stock">
                                                    <span class="error"
                                                          v-if="errors[index] !== undefined && errors[index].itemStock !== undefined">{{
                                                            errors[index].itemStock
                                                        }}</span>
                                                </td>
                                                <td>
                                                    <input type="number" @input="calculateTotal(field, index)" class="form-control" style="text-align: end"
                                                           v-model="field.itemValue" placeholder="Value">
                                                </td>
                                                <td>
                                                    <input readonly type="text" class="form-control" style="text-align: end"
                                                           v-model="field.totalValue" placeholder="Total">
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-danger btn-sm"
                                                            @click="removeRow(index)"><i
                                                        class="ti-close"></i></button>
                                                </td>
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
            production_code:'',
            items: [],
            locations: [],
            transfer_date: '',
            reference: '',
            dayStr: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
            fields: [
                {
                    item: '',
                    itemCode: '',
                    location: {
                        'Active': 'Y',
                        'LocationCode': 'L0001',
                        'LocationName': 'Default'
                    },
                    uom:'',
                    quantity: 0,
                    itemValue: 0,
                    LocationFromCode:'',
                    LocationToCode:'',
                    totalValue:'',
                    itemStock:0
                }
            ],
            errors: [],

        }
    },
    computed: {},
    mounted() {
        this.transfer_date = moment().format('yyyy-MM-DD');
        $('#add-edit-dept').on('hidden.bs.modal', () => {
            this.$emit('changeStatus')
        });
        bus.$on('add-edit-production', (row) => {
            if (row) {
                let instance = this;
                this.axiosGet('production/get-production-info/' + row.ProductionCode, function (response) {
                    instance.title = 'Update production';
                    instance.buttonText = "Update";
                    instance.buttonShow = true;
                    instance.actionType = 'edit';
                    instance.fields.splice(0, 1)
                    instance.getData();
                    var productionInfo = response.ProductionInfo;

                    //Master
                    instance.production_code = response.ProductionInfo[0].ProductionCode
                    instance.transfer_date = response.ProductionInfo[0].TransferDate
                    instance.updateCategoryCode = productionInfo[0].CategoryCode
                    instance.reference = response.ProductionInfo[0].Reference

                    instance.categoryType=[{
                        'Active': response.ProductionInfo[0].Active,
                        'CategoryCode': response.ProductionInfo[0].CategoryCode,
                        'CategoryName': response.ProductionInfo[0].CategoryName
                    }
                    ]

                    //Details
                    productionInfo.forEach(function (item,index) {
                        instance.fields.push({
                            item: {
                                'ItemName': item.ItemName,
                                'ItemCode': item.ItemCode,
                            },
                            itemCode: item.ItemCode,
                            location: {
                                'Active': 'Y',
                                'LocationCode': item.LocationCode,
                                'LocationName': item.LocationName
                            },
                            quantity: item.Quantity,
                            itemValue: item.Value,
                            LocationCode: item.LocationCode,
                            uom: item.UOM
                        })
                    });
                    instance.getItemByCategory();
                }, function (error) {

                });
            } else {
                this.title = 'Add transfer';
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
        bus.$off('add-edit-production')
    },
    methods: {
        closeModal() {
            $("#add-edit-dept").modal("toggle");
        },
        addRow() {
            this.fields.push({
                item: '',
                itemCode: '',
                location: {
                    'Active': 'Y',
                    'LocationCode': 'L0001',
                    'LocationName': 'Default'
                },
                quantity: 0,
                itemValue: 0,
                LocationCode:'',
                uom:'',
                itemStock:0
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
            this.axiosGet('transfer/supporting-data', function (response) {
                instance.category = response.category;
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
            let url = 'transfer/category-wise-item';
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
        checkItemStock(e,itemCode,key){
            let locationCode = e.target.value;
            let url = 'transfer/check-stock-item-wise';
            this.axiosPost(url, {
                itemCode:itemCode ,
                locationCode:locationCode ,
            }, (response) => {
                let instance = this;
               if(response.stock != null){
                   instance.fields[key].itemStock = response.stock ? parseFloat(response.stock.BatchQty): 0;
               }
               else{
                   instance.fields[key].itemStock =0
               }


            }, (error) => {
                this.errorNoti(error);
            })

        },
        checkFieldValue() {
            this.errors = [];
            let instance = this;
            this.fields.forEach(function (item, index) {
                if (item.itemCode === '' || item.location === ''
                    || item.quantity === '' || item.quantity <=0 || item.quantity >item.itemStock||  item.quantity === undefined ) {
                    instance.errors[index] = {
                        itemCode: item.itemCode === '' ? 'item Code is required' : '',
                        location: item.location === '' ? 'location  is required' : '',
                        quantity: (item.quantity === '' ||item.quantity <=0 ) ? 'quantity  is required' : '',
                        itemStock: (item.quantity >item.itemStock) ? 'stock not available' : '',

                    };
                }
            });
        },
        calculateTotal(data, index){
            if(data.quantity && data.itemValue){
                this.fields = this.fields.map((field, i) => {
                    if(index == i){
                        field.totalValue = parseInt(data.quantity * data.itemValue)
                    }
                    return field
                })
            }
        },
        onSubmit() {
            this.checkFieldValue();
            if (this.errors.length === 0) {
                this.$store.commit('submitButtonLoadingStatus', true);
                let url = '';
                var returnData = $('#return').prop('checked');
                var  submitUrl = '';
                if (this.actionType === 'add') {
                    submitUrl = 'transfer/add';
                }
                // if(returnData && this.actionType === 'edit' ){
                //     submitUrl = 'production/return';
                // }
                if(!returnData && this.actionType === 'edit' ){
                    submitUrl = 'transfer/update';
                }
                this.axiosPost(submitUrl, {
                    production_code: this.production_code,
                    transfer_date: this.transfer_date,
                    reference: this.reference,
                    categoryType: this.categoryType,
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
