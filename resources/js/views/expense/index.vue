<template>
    <div class="container-fluid">
        <breadcrumb :options="['Expense']">
            <button class="btn btn-primary" @click="addModal()">Add Expense</button>
        </breadcrumb>
        <div class="row" style="padding:8px 0px;">
            <div class="col-md-4">
                <button type="button" class="btn btn-success btn-sm" @click="exportData">Export to Excel</button>
            </div>
        </div>
        <advanced-datatable :options="tableOptions">
            <template slot="action" slot-scope="row">
                <a href="javascript:" @click="addModal(row.item)"> <i class="ti-pencil-alt">Edit</i></a>
                <!--        <a href="javascript:" @click="changePassword(row.item.UserId)"> <i class="ti-lock"></i></a>-->
            </template>
        </advanced-datatable>
        <add-edit-expense @changeStatus="changeStatus" v-if="loading"/>
        <reset-password @changeStatus="changeStatus" v-if="loading"/>
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
                source: 'expense/expense-list',
                search: true,
                slots: [7],
               // hideColumn: ['RoleID','UserId'],
                slotsName: ['action'],
                sortable: [11],
                pages: [20, 50, 100],
                addHeader: ['Action']
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
        bus.$off('export-data')
    },
    methods: {
        changeStatus() {
            this.loading = false
        },
        addModal(row = '') {
            this.loading = true;
            setTimeout(() => {
                bus.$emit('add-edit-expense', row);
            })
        },

        exportData() {
            bus.$emit('export-data','expense-list-'+moment().format('YYYY-MM-DD'))
        }
    }
}
</script>
