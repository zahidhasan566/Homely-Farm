<template>
    <div class="container-fluid">
        <breadcrumb :options="['Sales']">
            <button class="btn btn-primary" @click="addModal()">Add Sales</button>
        </breadcrumb>
        <div class="row" style="padding:8px 0px;">
            <div class="col-md-4">
                <button type="button" class="btn btn-success btn-sm" @click="exportData">Export to Excel</button>
                <button type="button" class="btn btn-warning btn-sm" @click="exportData">Invoice Format</button>
            </div>
        </div>
        <advanced-datatable :options="tableOptions">
            <template slot="action" slot-scope="row" v-if="row.item.Returned === 'No'">
                <a href="javascript:" @click="addModal(row.item)"> <i class="ti-pencil-alt">Edit</i></a>
                <!--        <a href="javascript:" @click="changePassword(row.item.UserId)"> <i class="ti-lock"></i></a>-->
            </template>
            <template slot="print" slot-scope="row" >
                <router-link class="btn btn-primary" style="font-size: 12px;width:65px;padding: 2px 0px" target='_blank' :to="{path: `${baseurl()}`+'sales-print?action_type=print&SalesCode='+row.item.SalesCode}"><i class="fa fa-print">Print</i></router-link>
<!--                <a href="javascript:" @click="addModal(row.item)"><i class="fa fa-print">Print</i></a>-->
            </template>
        </advanced-datatable>
        <add-edit-sales @changeStatus="changeStatus" v-if="loading"/>
        <reset-password @changeStatus="changeStatus" v-if="loading"/>
    </div>
</template>
<script >

import {bus} from "../../app";
import {Common} from "../../mixins/common";
import moment from "moment";
import {baseurl} from "../../base_url";

export default {
    mixins: [Common],
    data() {
        return {
            tableOptions: {
                source: 'sales/list',
                search: true,
                slots: [9,10],
                slotsName: ['action','print'],
                sortable: [2],
                pages: [20, 50, 100],
                addHeader: ['Action','print']
            },
            loading: false,
            cpLoading: false
        }
    },
    mounted() {
        bus.$off('changeStatus',function () {
            this.changeStatus()
        })
    },
    destroyed() {
        bus.$off('add-edit-sales')
    },
    methods: {
        baseurl() {
            return baseurl
        },
        changeStatus() {
            this.loading = false
        },
        addModal(row = '') {
            this.loading = true;
            setTimeout(() => {
                bus.$emit('add-edit-sales', row);
            })
        },

        exportData() {
            bus.$emit('export-data','sales-list-'+moment().format('YYYY-MM-DD'))
        }
    }
}
</script>
