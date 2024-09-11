<template>
    <div class="container-fluid">
        <div class="row" style="padding:8px 0px;">
            <div class="col-md-4">
                <button type="button" class="btn btn-success btn-sm" @click="exportData">Export to Excel</button>
            </div>
        </div>
        <div class="col-md-12" style="text-align: end">
            <button style="padding: 8px 9px" type="button"   class="btn btn-success btn-sm" @click="checkSubmit">submit</button>
        </div>
        <advanced-datatable :options="tableOptions">
            <template slot="Paid" slot-scope="row">
                <span v-if="row.item.Paid === 'N'">
                    No
                </span>
                <span v-else>
                   Yes
                </span>
            </template>

            <template slot="Payment" slot-scope="row">
                <input  @input="checkPayment(row.item,$event)" v-model="row.item.Payment" type="number" class="form-control">
            </template>
        </advanced-datatable>
        <add-edit-purchase @changeStatus="changeStatus" v-if="loading"/>
        <reset-password @changeStatus="changeStatus" v-if="loading"/>
        <div class="modal fade" v-if="confirmPaymentFlag" id="add-edit-dept" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="modal-title modal-title-font" id="exampleModalLabel">Confirm Payment</div>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" @click="closeModal">
                            Close
                        </button>
                    </div>
                            <div class="modal-body">
                                <div class="row">
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table
                                            class="table  table-bordered table-striped   nowrap dataTable no-footer dtr-inline table-sm">
                                            <thead class="thead-dark">
                                            <tr>
                                                <th>Sales Code <span class="required-field">*</span></th>
                                                <th>Sales Date <span class="required-field">*</span></th>
                                                <th>Customer Code<span class="required-field">*</span></th>
                                                <th>Customer Name<span class="required-field">*</span></th>
                                                <th>Paid <span class="required-field">*</span></th>
                                                <th>Value<span class="required-field">*</span></th>
                                                <th>Paid Amount<span class="required-field">*</span></th>
                                                <th>Payment<span class="required-field">*</span></th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr v-for="(field,index) in finalPaymentRows" :key="index">
                                              <td>{{field.SalesCode}}</td>
                                              <td>{{field.SalesDate}}</td>
                                              <td>{{field.CustomerCode}}</td>
                                              <td>{{field.CustomerName}}</td>
                                              <td>{{field.Paid}}</td>
                                              <td>{{field.Value}}</td>
                                              <td>{{field.PaidAmount}}</td>
                                              <td style="text-align: end">{{field.CurrentPayment}}</td>
                                            </tr>
                                            </tbody>
                                        </table>

                                        <div class="col-md-12" style="text-align: end;padding-top: 20px">
                                            <button type="button"   class="btn btn-success btn-sm" @click="onSubmit">Confirm </button>
                                        </div>

                                    </div>
                                </div>
                            </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script >

import {bus} from "../../app";
import {Common} from "../../mixins/common";
import moment from "moment";

export default {
    mixins: [Common],
    data() {
        return {
            tableOptions: {
                source: 'customers/due-list',
                search: true,
                slots: [4,7],
               // hideColumn: ['RoleID','UserId'],
                slotsName: ['Paid','Payment'],
                sortable: [2],
                pages: [20, 50, 100],
                addHeader: ['Payment']
            },
            loading: false,
            cpLoading: false,
            errors:[],
            finalPaymentRows:[],
            confirmPaymentFlag:false
        }
    },
    mounted() {
        bus.$off('changeStatus',function () {
            this.changeStatus()
        })
    },
    destroyed() {
        bus.$off('export-data')
    },
    methods: {
        changeStatus() {
            this.loading = false
        },
        addModal(row = '') {
            this.loading = true;
            setTimeout(() => {
                bus.$emit('add-edit-purchase', row);
            })
        },
        closeModal() {
            $("#add-edit-dept").modal("toggle");
        },
        checkPayment(item,e){
            let currentAmount  =  parseFloat(e.target.value)
            let existItem = this.finalPaymentRows.find(existingItem => existingItem.SalesCode === item.SalesCode);
            if(currentAmount > parseFloat(item.Value)){
                let index = this.finalPaymentRows.findIndex(f => f.SalesCode === item.SalesCode);
                if (index > -1) {
                    this.finalPaymentRows.splice(index, 1);
                }
                this.errors.push(1)
                this.errorNoti('Payment is greater than total value');
            }
            else{
                this.errors=[]
                if(parseFloat(item.Value)>0){
                    if (existItem) {
                        existItem.CurrentPayment = currentAmount
                    } else {
                        this.finalPaymentRows.push({
                            SalesCode: item.SalesCode,
                            SalesDate: item.SalesCode,
                            CustomerCode: item.CustomerCode,
                            CustomerName: item.CustomerName,
                            Paid: item.Paid,
                            CurrentPayment: currentAmount,
                            Value: item.Value,
                            PaidAmount: item.PaidAmount,
                        })
                    }
                }
            }
        },
        checkSubmit(){
            if (this.finalPaymentRows.length> 0) {
                this.confirmPaymentFlag = true
                setTimeout(() => {
                    $("#add-edit-dept").modal("toggle");
                })
            }
        },
        onSubmit() {
            if (this.finalPaymentRows.length> 0) {
                this.$store.commit('submitButtonLoadingStatus', true);
                var  submitUrl = 'customers/due-list-update';
                this.axiosPost(submitUrl, {
                    finalPaymentRows : this.finalPaymentRows,
                }, (response) => {
                    this.successNoti(response.message);
                    $("#add-edit-dept").modal("toggle");
                    this.$router.go(this.$router.currentRoute)
                    bus.$emit('refresh-datatable');
                    this.$store.commit('submitButtonLoadingStatus', false);
                }, (error) => {
                    this.errorNoti(error);
                    this.$store.commit('submitButtonLoadingStatus', false);
                })
            }
            console.log(this.finalPaymentRows)
        },

        exportData() {
            bus.$emit('export-data','customer-due-list-'+moment().format('YYYY-MM-DD'))
        }
    }
}
</script>
