<template>
    <div style="color: #000000 !important;background:#ffffff !important;">
        <div>
            <div style="width: 0;float: left">
                <img style="text-align: left" src="assets/images/hfLogo.jpeg" alt="" height="100">
            </div>
            <div><h1 style="text-align:center">Homely Farm</h1></div>
        </div>

        <div style="text-align:center;">
            <div>
                <p> <span style="font-weight: bold"> স্কাইল্যান্ড ট্রেডিংয়ের</span>  একটি সিস্টার কনসার্ন </p>
                <p>হোল্ডিং নং-১০১৩, ওয়ার্ড নং: ০৯, গ্রাম- সুন্দরপুর, চৌধুরী বাড়ি</p>
                <p>ডাকঘর: মোহাম্মদ আলী বাজার, পিএস: ফেনী সদর, জেলা: ফেনী</p>
                <p>মোবাইল : ০১৬১১-৫৬৫৬২৪, ০১৭১৬-০৩৭৪৪৬</p>
                <p><span
                    style="border-radius: 15px;padding: 5px 5px;font-weight:bold;font-size:20px ">নগদ স্মারক/চালান</span>
                </p>
            </div>

            <div>
                <p style="text-align:start">গ্রাহক তথ্য: </p>
                <div class="table-responsive">
                    <table style="border:none"
                           class="table  table-bordered table-striped   nowrap dataTable no-footer dtr-inline table-sm">
                        <thead class="thead-dark">

                        </thead>
                        <tbody>
                        <tr>
                            <th style="font-weight:bold">চালান নং</th>
                            <td>{{ salesCode }}</td>
                            <th style="font-weight:bold">তারিখ</th>
                            <td>{{ formatDate(sales_date) }}</td>
                        </tr>
                        <tr>
                            <th style="font-weight:bold">গ্রাহকের নাম</th>
                            <td>{{ CustomerNameBangla }}</td>
                            <th style="font-weight:bold">মোবাইল</th>
                            <td>{{ MobileNo }}</td>
                            <td></td>
                        </tr>
                        <tr>
                            <th style="font-weight:bold">ঠিকানা</th>
                            <td>{{ AddressBangla }}</td>
                        </tr>
                        </tbody>
                    </table>

                </div>
                <p style="text-align:start;padding-top: 20px">
                    বিস্তারিত: </p>
                <div class="table-responsive">
                    <table
                        class="table  table-bordered table-striped   nowrap dataTable no-footer dtr-inline table-sm">
                        <thead style="color:#000000;background:#ffffff">
                        <tr>
                            <th style="font-weight:bold">সিরিয়াল</th>
                            <th style="font-weight:bold">বর্ণনা</th>
                            <th style="font-weight:bold">পরিমাণ</th>
                            <th style="font-weight:bold" v-if="actionType==='challan'">হার</th>
                            <th style="font-weight:bold" v-if="actionType==='challan'">মোট</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="(field,index) in fields" :key="index">
                            <td>{{ index + 1 }}</td>
                            <td>
                                {{ field.item }}
                            </td>
                            <td style="text-align:end">{{field.quantity }}</td>
                            <td v-if="actionType==='challan'" style="text-align:end">{{ field.unitPrice }}</td>
                            <td v-if="actionType==='challan'" style="text-align:end">
                                {{ field.quantity * field.unitPrice }}
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="font-weight:bold;text-align: end;">
                                মোট পরিমাণ</td>
                            <td style="text-align: end;">{{totalQty}}</td>
                            <td   v-if="actionType==='challan'" style="font-weight:bold;text-align: end;">Total Amount</td>
                            <td   v-if="actionType==='challan'" style="text-align: end;">{{totalAmount}}</td>

                        </tr>
                        <tr v-if="actionType==='challan'">
                            <td colspan="4" style="font-weight:bold;text-align: end;">ডেলিভারি চার্জ</td>
                            <td style="text-align: end;">{{deliveryCharge}}</td>

                        </tr>
                        <tr v-if="actionType==='challan'">
                            <td colspan="4" style="font-weight:bold;text-align: end;">
                                গ্র্যান্ড টোটাল</td>
                            <td style="text-align: end;">{{ parseFloat(totalValue)-parseFloat(deliveryCharge) }}</td>

                        </tr>
                        <tr v-if="actionType==='challan'">
                            <td colspan="4" style="font-weight:bold;text-align: end;">প্রদত্ত পরিমাণ</td>
                            <td style="text-align: end;">{{ paidAmount }}</td>

                        </tr>
                        <tr v-if="actionType==='challan'">
                            <td colspan="4" style="font-weight:bold;text-align: end;">বাকি</td>
                            <td style="text-align: end;">{{ parseFloat(totalValue) - parseFloat(paidAmount) -parseFloat(deliveryCharge) }}</td>

                        </tr>
                        </tbody>
                    </table>

                </div>

            </div>
        </div>
        <div style="display: flex">
            <p style="margin-top:50px;color:#000000;font-weight:bold;width:50%">আপনার ক্রয়ের জন্য ধন্যবাদ </p>
            <p style="margin-top:50px;color:#000000;font-weight:bold;width:50%;text-align:end">
                <span>-----------------------</span>
                <br>
                <span>স্বাক্ষর</span>
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
            sales_date: '2023-09-14',
            items: [],
            customers: [],
            salesCode: '',
            locations: [],
            reference: '',
            allStock: [],
            customerName: '',
            CustomerNameBangla: '',
            AddressBangla: '',
            MobileNo: '',
            customerAddress: '',
            paid: '',
            totalValue: 0,
            partialAmount: 0,
            totalQty:0,
            totalAmount:0,
            paidAmount: 0,
            deliveryCharge:0,
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
        this.actionType = this.$route.query.action_type

        let instance = this
        this.axiosGet('sales/get-sales-info/' + salesCode, function (response) {
            instance.title = 'Update Sales';
            instance.buttonText = "Update";
            instance.buttonShow = true;
            var salesInfo = response.SalesInfo;
            instance.fields.splice(0, 1)
            console.log(salesInfo);

            //Master
            instance.salesCode = response.SalesInfo[0].SalesCode
            instance.sales_date = response.SalesInfo[0].SalesDate
            instance.updateCategoryCode = response.SalesInfo[0].CategoryCode
            instance.reference = response.SalesInfo[0].Reference
            instance.paid = parseFloat(response.SalesInfo[0].Paid)
            instance.paidAmount = parseFloat(response.SalesInfo[0].PaidAmount)
            instance.deliveryCharge = parseFloat(response.SalesInfo[0].DeliveryCharge)
            instance.totalValue = parseFloat(response.SalesInfo[0].totalValue)
            instance.customerName = response.SalesInfo[0].CustomerName
            instance.CustomerNameBangla = response.SalesInfo[0].CustomerNameBangla
            instance.customerAddress = response.SalesInfo[0].Address
            instance.AddressBangla = response.SalesInfo[0].AddressBangla
            instance.MobileNo = response.SalesInfo[0].MobileNo

            console.log('t',instance.totalValue)

            //Details
            salesInfo.forEach(function (item, index) {
                instance.fields.push({
                    item: item.ItemCode + '-' + item.ItemName,
                    itemCode: item.ItemCode,
                    unitPrice: item.UnitPrice,
                    quantity: item.Quantity,
                    totalValue: item.Value,
                })
                instance.totalQty += parseFloat(item.Quantity)
                instance.totalAmount += parseFloat(item.Value)
            });


            setTimeout(() => {
                window.print();
            },1000)


        }, function (error) {

        })
    },
    destroyed() {
        bus.$off('add-edit-sales')
    },
    methods: {
        formatDate(dateStr) {
            const date = new Date(dateStr);
            if (isNaN(date)) return 'Invalid Date';
            return new Intl.DateTimeFormat('bn-BD', {
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            }).format(date);
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
