<template>
    <div class="container-fluid">
        <breadcrumb :options="['Product Transfer']">
            <button class="btn btn-primary" @click="addModal()">Add Transfer</button>
        </breadcrumb>
        <div class="row" style="padding:8px 0px;">
            <div class="col-md-4">
                <button type="button" class="btn btn-success btn-sm" @click="exportData">Export to Excel</button>
            </div>
        </div>
        <advanced-datatable :options="tableOptions">
            <template slot="action" slot-scope="row" v-if="row.item.Returned === 'No'">
                <!-- <a href="javascript:" @click="addModal(row.item)"> <i class="ti-pencil-alt">Edit</i></a> -->
            </template>
        </advanced-datatable>
        <add-edit-transfer @changeStatus="changeStatus" v-if="loading"/>
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
                source: 'transfer/list',
                search: true,
                slots: [7],
               // hideColumn: ['RoleID','UserId'],
                slotsName: ['action'],
                sortable: [2],
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
                bus.$emit('add-edit-production', row);
            })
        },

        exportData() {
            bus.$emit('export-data','production-list-'+moment().format('YYYY-MM-DD'))
        }
    }
}
</script>
