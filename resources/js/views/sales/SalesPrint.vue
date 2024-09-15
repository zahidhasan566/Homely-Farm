<template>
    <div>
    <div style="text-align:center;">
        <div>
            <h1 style="text-align:center">Homely Farm</h1>
            <p>A Sister Concern of SKYLAND TRADING</p>
            <p>Holding No-1013, Ward No: 09, Village- Sundarpur, Chowdhury Bari</p>
            <p>Post Office: Mohamad Ali Bazar, PS: Feni Sadar, District : Feni</p>
            <p>Mobile : 01611-565624, 01716-037446 </p>
            <p><span
                style="background: #0b0b0b;color: #fff3cd; border-radius: 15px;padding: 5px 5px ">Cash Memo/Challan</span>
            </p>
        </div>


        <div>
            <div class="table-responsive">
                <table style="border:none"
                       class="table  table-bordered table-striped   nowrap dataTable no-footer dtr-inline table-sm">
                    <thead class="thead-dark">

                    </thead>
                    <tbody>
                    <tr>
                        <th style="font-weight:bold">Invoice No</th>
                        <td>{{ salesCode }}</td>
                        <th>Date</th>
                        <td>{{ sales_date }}</td>
                    </tr>
                    <tr>
                        <th style="font-weight:bold">Customer Name</th>
                        <td>{{ customerName }}</td>
                        <th>Mobile</th>
                        <td></td>
                    </tr>
                    <tr>
                        <th style="font-weight:bold">Address</th>
                        <td></td>
                    </tr>
                    </tbody>
                </table>

            </div>
            <div class="table-responsive">
                <table
                    class="table  table-bordered table-striped   nowrap dataTable no-footer dtr-inline table-sm">
                    <thead style="color:#000000;background:#ffffff">
                    <tr>
                        <th>SL</th>
                        <th>Description</th>
                        <th>QTY</th>
                        <th>Rate</th>
                        <th>Total</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(field,index) in fields" :key="index">
                        <td>{{index+1}}</td>
                        <td>
                            {{ field.item }}
                        </td>
                        <td style="text-align:end">{{field.quantity}} </td>
                        <td style="text-align:end">{{field.unitPrice}}</td>
                        <td style="text-align:end">{{field.totalValue}}</td>
                    </tr>
                    </tbody>
                </table>

            </div>

        </div>
    </div>
    <div style="display: flex">
        <p style="margin-top:50px;color:#000000;font-weight:bold;width:50%">Thank You For Your Purchase</p>
        <p style="margin-top:50px;color:#000000;font-weight:bold;width:50%;text-align:end">
            <span>-----------------------</span>
            <br>
            <span>signature</span>
            </p>
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
            updateCategoryCode: '',
            sales_date: '',
            items: [],
            customers: [],
            salesCode: '',
            locations: [],
            reference: '',
            allStock: [],
            customerName: '',
            customerAddress: '',
            paid: '',
            totalValue: 0,
            partialAmount: 0,
            purchaseType: [{
                'PurchaseTypeCode': 'direct',
                'PurchaseTypeName': 'direct',
            },
                {
                    'PurchaseTypeCode': 'indirect ',
                    'PurchaseTypeName': 'indirect ',
                }],
            customerTypeVal: '',
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
                    unitPrice: 0,
                    quantity: 0,
                    itemValue: 0,
                    stock: 0,
                    LocationCode: '',
                    uom: '',
                }
            ],
            errors: [],
            amountErrors: [],

        }
    },
    computed: {},
    mounted() {
        this.sales_date = moment().format('yyyy-MM-DD');
        $('#add-edit-dept').on('hidden.bs.modal', () => {
            this.$emit('changeStatus')
        });

        let salesCode = this.$route.query.SalesCode
        console.log('dd', salesCode)
        let instance = this
        this.axiosGet('sales/get-sales-info/' + salesCode, function (response) {
            instance.title = 'Update Sales';
            instance.buttonText = "Update";
            instance.buttonShow = true;
            instance.actionType = 'edit';
            var salesInfo = response.SalesInfo;
            instance.fields.splice(0, 1)
            console.log(salesInfo);

            //Master
            instance.salesCode = response.SalesInfo[0].SalesCode
            instance.sales_date = response.SalesInfo[0].SalesDate
            instance.updateCategoryCode = response.SalesInfo[0].CategoryCode
            instance.reference = response.SalesInfo[0].Reference
            instance.paid = response.SalesInfo[0].Paid
            instance.partialAmount = response.SalesInfo[0].PaidAmount
            instance.totalValue = response.SalesInfo[0].totalValue
            instance.customerName = response.SalesInfo[0].CustomerName
            instance.customerAddress = ''

            //Details
            salesInfo.forEach(function (item, index) {
                instance.fields.push({
                    item: item.ItemCode+'-'+item.ItemName,
                    itemCode: item.ItemCode,
                    unitPrice: item.UnitPrice,
                    quantity: item.Quantity,
                    totalValue: item.Value,
                })
            });

             //window.print();

        }, function (error) {

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
                unitPrice: 0,
                quantity: 0,
                itemValue: 0,
                stock: 0,
                LocationCode: '',
                uom: '',
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

            }, function (error) {
            });
        },
        getItemByCategory() {
            let instance = this;
            let tableField = instance.fields;

            if (instance.actionType === 'add') {
                tableField.forEach(function (item, index) {
                    tableField.splice(index, 1)
                });
            }

            let categoryCode = '';
            if (instance.actionType === 'add') {
                categoryCode = instance.categoryType.CategoryCode;
            } else {
                categoryCode = instance.updateCategoryCode;
            }
            let url = 'sales/category-wise-item';
            this.axiosPost(url, {
                CategoryCode: categoryCode,
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
        checkUpdateStock(e, key) {
            let instance = this;
            var currentLocationCode = e.target.value;
            var currentItemCode = instance.fields[key].itemCode
            let updateStock = this.allStock.find((row) => {
                return (row.ItemCode === currentItemCode && row.LocationCode === currentLocationCode);
            });
            instance.fields[key].stock = updateStock ? updateStock.stock : '';

        },
        setValue(index) {
            let instance = this;
            let currentPrice = parseFloat(instance.fields[index].unitPrice);
            let currentQuantity = parseFloat(instance.fields[index].quantity);
            let currentStock = parseFloat(instance.fields[index].stock);

            if (currentPrice && currentQuantity) {
                instance.fields[index].itemValue = currentPrice * currentQuantity;
                //compare with stock
                if (currentQuantity > currentStock) {
                    this.errorNoti('Stock is not available');
                }
            }

            //Total Value

            let totalValueTemp = 0;
            instance.fields.forEach((item) => {
                totalValueTemp += item.itemValue
            })
            instance.totalValue = totalValueTemp

        },
        checkWithTotalAmount() {
            console.log('p', this.partialAmount, 't', this.totalValue)

            if (parseFloat(this.partialAmount) > parseFloat(this.totalValue)) {
                this.errorNoti('Partial Amount is greater than Total Value');
                this.amountErrors.push('error')
            } else if (parseFloat(this.partialAmount) === parseFloat(this.totalValue)) {
                this.amountErrors.push('error')
                this.errorNoti('Partial Amount will not equal to Total Value');
            } else {
                this.amountErrors = []
            }

        },
        checkFieldValue() {
            this.errors = [];
            let instance = this;
            this.fields.forEach(function (item, index) {
                if (item.itemCode === '' || item.location === ''
                    || item.quantity === '' || item.quantity <= 0 || item.quantity === undefined || item.itemValue === '' || item.itemValue <= 0
                    || item.stock === '' || item.stock === 0
                ) {
                    instance.errors[index] = {
                        itemCode: item.itemCode === '' ? 'item Code is required' : '',
                        stock: item.stock === '' ? 'stock is required' : '',
                        unitPrice: item.unitPrice === '' ? 'Unit Price is required' : '',
                        quantity: (item.quantity === '' || item.quantity <= 0) ? 'quantity is required' : '',
                        itemValue: (item.itemValue === '' || item.itemValue <= 0) ? 'item value  is required' : '',

                    };
                }
                if (parseFloat(item.quantity) > parseFloat(item.stock)) {
                    instance.errors[index] = {
                        stock: item.stock < item.quantity ? 'Stock is not available' : '',
                    };
                }
            });
        },
        onSubmit() {
            this.checkFieldValue();
            this.checkWithTotalAmount();
            var returnData = $('#return').prop('checked');
            if (this.errors.length === 0 && this.amountErrors.length === 0) {
                this.$store.commit('submitButtonLoadingStatus', true);
                var submitUrl = '';
                if (this.actionType === 'add') {
                    submitUrl = 'sales/add';
                }
                if (returnData && this.actionType === 'edit') {
                    submitUrl = 'sales/return';
                }
                if (!returnData && this.actionType === 'edit') {
                    submitUrl = 'sales/update';
                }
                this.axiosPost(submitUrl, {
                    salesCode: this.salesCode,
                    sales_date: this.sales_date,
                    reference: this.reference,
                    customerTypeVal: this.customerTypeVal,
                    categoryType: this.categoryType,
                    paid: this.paid,
                    totalValue: this.totalValue,
                    partialAmount: this.partialAmount,
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
