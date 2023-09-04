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
                                        <ValidationProvider name="production_date" mode="eager"
                                                            v-slot="{ errors }">
                                            <div class="form-group">
                                                <label for="name">Sales Date<span class="error">*</span></label>
                                                <datepicker v-model="sales_date" :dayStr="dayStr"
                                                            placeholder="YYYY-MM-DD" :firstDayOfWeek="0"/>
                                                <span class="error-message"> {{ errors[0] }}</span>
                                            </div>
                                        </ValidationProvider>
                                    </div>
                                    <div class="col-12 col-md-6">
                                            <div class="form-group">
                                                <label for="name">Reference</label>
                                                <input type="text" class="form-control"
                                                       :class="{'error-border': errors[0]}" id="Reference"
                                                       v-model="reference" name="staff-name" placeholder="Reference">
                                                <span class="error-message"> {{ errors[0] }}</span>
                                            </div>

                                    </div>
                                    <div class="col-12 col-md-4">
                                        <ValidationProvider name="UserType" mode="eager" rules="required"
                                                            v-slot="{ errors }">
                                            <div class="form-group">
                                                <label for="user-type">Category <span class="error">*</span></label>
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
                                        <ValidationProvider name="purchaseTypeVal" mode="eager" rules="required"
                                                            v-slot="{ errors }">
                                            <div class="form-group">
                                                <label for="user-type">Customer <span class="error">*</span></label>
                                                <multiselect v-model="customerTypeVal" :options="customers"
                                                             :multiple="false"
                                                             :close-on-select="true"
                                                             :clear-on-select="false" :preserve-search="true"
                                                             placeholder="Select Customer"
                                                             label="CustomerWithCode" track-by="CustomerCode">

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
                                            <label for="payment-required-by">Add Purchase</label>
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
                                                <th>Location<span class="required-field">*</span></th>
                                                <th>Stock</th>
                                                <th>Unit Price <span class="required-field">*</span></th>
                                                <th>Quantity<span class="required-field">*</span></th>
                                                <th>Value<span class="required-field">*</span></th>
                                                <th>Action</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr v-for="(field,index) in fields" :key="index">
                                                <td>
                                                    <multiselect v-model="field.item" :options="items"
                                                                 :multiple="false"
                                                                 @input="setItemCode(index)"
                                                                 :close-on-select="true"
                                                                 :clear-on-select="false" :preserve-search="true"
                                                                 placeholder="Select Category"
                                                                 label="ItemName" track-by="ItemCode">

                                                    </multiselect>
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
                                                    <multiselect v-model="field.location" :options="locations"
                                                                 :multiple="false"
                                                                 :close-on-select="true"
                                                                 :clear-on-select="false" :preserve-search="true"
                                                                 placeholder="Select Category"
                                                                 label="LocationName" track-by="LocationCode">

                                                    </multiselect>
                                                    <span class="error"
                                                          v-if="errors[index] !== undefined && errors[index].location !== undefined">{{
                                                            errors[index].location
                                                        }}</span>
                                                </td>
                                                <td>
                                                    <input type="text"  class="form-control"  @input="setValue(index)" readonly
                                                           v-model="field.stock" placeholder="stock" min="1">
                                                    <span class="error"
                                                          v-if="errors[index] !== undefined && errors[index].stock !== undefined">{{
                                                            errors[index].stock
                                                        }}</span>
                                                </td>
                                                <td>
                                                    <input type="text"  class="form-control"  @input="setValue(index)"
                                                           v-model="field.unitPrice" placeholder="unit Price" min="1">
                                                    <span class="error"
                                                          v-if="errors[index] !== undefined && errors[index].unitPrice !== undefined">{{
                                                            errors[index].unitPrice
                                                        }}</span>
                                                </td>
                                                <td>
                                                    <input type="text"  class="form-control"
                                                           v-model="field.quantity" placeholder="quantity"  @input="setValue(index)" min="1">
                                                    <span class="error"
                                                          v-if="errors[index] !== undefined && errors[index].quantity !== undefined">{{
                                                            errors[index].quantity
                                                        }}</span>

                                                </td>
                                                <td>
                                                    <input type="text" class="form-control" readonly
                                                           v-model="field.itemValue" placeholder="Value" min="1">
                                                    <span class="error"
                                                          v-if="errors[index] !== undefined && errors[index].itemValue !== undefined">{{
                                                            errors[index].itemValue
                                                        }}</span>
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
            sales_date:'',
            items: [],
            customers:[],
            salesCode:'',
            locations: [],
            reference: '',
            allStock: [],
            purchaseType:[{
                'PurchaseTypeCode': 'direct',
                'PurchaseTypeName': 'direct',
            },
                {
                    'PurchaseTypeCode': 'indirect ',
                    'PurchaseTypeName': 'indirect ',
                }] ,
            customerTypeVal:'',
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
                    unitPrice:0,
                    quantity: 0,
                    itemValue: 0,
                    stock:0
                }
            ],
            errors: [],

        }
    },
    computed: {},
    mounted() {
        this.sales_date = moment().format('yyyy-MM-DD');
        $('#add-edit-dept').on('hidden.bs.modal', () => {
            this.$emit('changeStatus')
        });
        bus.$on('add-edit-sales', (row) => {
            if (row) {
                let instance = this;
                this.axiosGet('sales/get-sales-info/' + row.SalesCode, function (response) {
                    console.log(response)
                    instance.title = 'Update Sales';
                    instance.buttonText = "Update";
                    instance.buttonShow = true;
                    instance.actionType = 'edit';
                    instance.fields.splice(0, 1)
                    instance.getData();
                    var salesInfo = response.SalesInfo;

                    //Master
                    instance.salesCode = response.SalesInfo[0].SalesCode
                    instance.sales_date = response.SalesInfo[0].SalesDate
                    instance.updateCategoryCode = response.SalesInfo[0].CategoryCode
                    instance.reference = response.SalesInfo[0].Reference

                    instance.categoryType=[{
                        'Active': salesInfo[0].Reference,
                        'CategoryCode': salesInfo[0].CategoryCode,
                        'CategoryName': salesInfo[0].CategoryName
                    }]
                    instance.customers=[{
                        'CustomerWithCode': salesInfo[0].CustomerWithCode,
                        'CustomerCode': salesInfo[0].CustomerCode,
                    }]
                    // instance.purchaseTypeVal=[{
                    //     'PurchaseTypeCode': purchaseInfo[0].PurchaseType,
                    //     'PurchaseTypeName': purchaseInfo[0].PurchaseType,
                    // }]

                    //Details
                    salesInfo.forEach(function (item,index) {
                       let itemCode2 = item.ItemCode;
                       // let test = (instance.allStock).find(function (item2){
                       //     return item2 ===itemCode2;
                       // })
                        //console.log(test)
                       // instance.checkUpdateStock(itemCode2,index)
                        instance.fields.push({
                            item: {
                                'ItemName': item.ItemName,
                                'ItemCode': item.ItemCode,
                            },
                            itemCode: item.ItemCode,
                            location: {
                                'Active': 'Y',
                                'LocationCode': 'L0001',
                                'LocationName': 'Default'
                            },
                            unitPrice: item.UnitPrice,
                            quantity: item.Quantity,
                            itemValue: item.Value,
                        })
                        console.log(instance.allStock)
                    });
                    instance.getItemByCategory();

                }, function (error) {

                });
            } else {
                this.title = 'Add Sales';
                this.buttonText = "Add";
                this.UserId = '';

                this.status = '';
                this.buttonShow = true;
                this.actionType = 'add'
                this.getData();
            }
            $("#add-edit-dept").modal("toggle");
            // $(".error-message").html("");
        })
    },
    destroyed() {
        bus.$off('add-edit-sales')
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
                unitPrice:0,
                quantity: 0,
                itemValue: 0,
                stock:0
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
            this.axiosGet('sales/supporting-data', function (response) {
                instance.allStock = response.allStock;
                instance.category = response.category;
                instance.customers = response.customer;
                console.log(instance.allStock)
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
            let url = 'sales/category-wise-item';
            this.axiosPost(url, {
                CategoryCode:categoryCode ,
            }, (response) => {
                instance.items = response.items;
                instance.locations = response.locations;
            }, (error) => {
                this.errorNoti(error);

            })
        },
        setItemCode(index) {
            let instance = this;
            instance.fields[index].itemCode = instance.fields[index].item.ItemCode;
            this.checkStock((instance.fields[index].item.ItemCode),index);
        },
        checkUpdateStock(itemCode){

            return itemCode;
        },
        checkStock(itemCode,index){
            let instance = this;

            let url = 'sales/category-wise-item-stock';
            this.axiosPost(url, {
                ItemCode:itemCode ,
            }, (response) => {
                console.log(response.totalStock)
                instance.fields[index].stock = response.totalStock;
            }, (error) => {
                this.errorNoti(error);

            })

        },
        setValue(index){

            let instance = this;
            let currentPrice  = instance.fields[index].unitPrice;
            let currentQuantity  = instance.fields[index].quantity;
            let currentStock  = instance.fields[index].stock;

            console.log(typeof (currentStock) );
            if( currentPrice && currentQuantity){
                instance.fields[index].itemValue = currentPrice*currentQuantity ;
            }
            //compare with stock
            if(currentQuantity > currentStock){
                this.errorNoti('Stock is not available');
            }
        },
        checkFieldValue() {
            this.errors = [];
            let instance = this;
            this.fields.forEach(function (item, index) {
                if (item.item === '' || item.itemCode === '' || item.location === ''
                    || item.quantity === '' ||  item.quantity <= 0 || item.quantity === undefined || item.itemValue === '' || item.itemValue <= 0) {
                    instance.errors[index] = {
                        item: item.item === '' ? 'Item is required' : '',
                        itemCode: item.itemCode === '' ? 'item Code is required' : '',
                        unitPrice: item.unitPrice === '' ? 'Unit Price is required' : '',
                        quantity: (item.quantity === '' || item.quantity <=0) ? 'quantity is required' : '',
                        itemValue: (item.itemValue === ''|| item.itemValue <=0) ? 'item value  is required' : '',

                    };
                }
                if(item.quantity> item.stock){
                    instance.errors[index]={
                      stock: item.stock < item.quantity ? 'Stock is not available':'',
                    };
                }
            });
        },
        onSubmit() {
            this.checkFieldValue();
            var returnData = $('#return').prop('checked');
            if (this.errors.length === 0) {
                this.$store.commit('submitButtonLoadingStatus', true);
                var  submitUrl = '';
                if (this.actionType === 'add') {
                    submitUrl = 'sales/add';
                }
                if(returnData && this.actionType === 'edit' ){
                    submitUrl = 'purchase/return';
                }
                if(!returnData && this.actionType === 'edit' ){
                    submitUrl = 'sales/update';
                }
                this.axiosPost(submitUrl, {
                    salesCode : this.salesCode,
                    sales_date: this.sales_date,
                    reference: this.reference,
                    customerTypeVal: this.customerTypeVal,
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
