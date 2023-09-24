<template>
    <div class="container-fluid">
        <breadcrumb :options="['Current Stock Report']">
        </breadcrumb>
        <div class="row" style="padding:8px 0px;">
            <div class="col-md-4">
                <button type="button" class="btn btn-success btn-sm" @click="exportData">Export to Excel</button>
            </div>
        </div>
        <advanced-datatable :options="tableOptions" v-if="showTable">
            <template  slot="currentStock" slot-scope="row">
                <p style="text-align:right;margin: 0;padding: 0;">   {{row.item.ClosingQty}}</p>
            </template>
        </advanced-datatable>
    </div>
</template>
<script>

import {bus} from "../../app";
import {Common} from "../../mixins/common";
import moment from "moment";
export default {
    mixins: [Common],

    data() {
        return {
            showTable: false,
            tableOptions: {},
            loading: false,
            cpLoading: false,
        }
    },
    mounted() {
        let instance = this;
        instance.getData();
    },
    // destroyed() {
    //     bus.$off('export-data')
    // },
    methods: {
        changeStatus() {
            this.loading = false
        },
        getData() {
            let instance = this;
            instance.loadDatatable();
        },
        loadDatatable(response) {
            this.showTable = true
            this.tableOptions = {
                source: 'report/current-stock',
                search: true,
                slots: [3],
                //sortable: [3],
                slotsName: ['currentStock'],
                pages: [20, 50, 100],
            }
        },
        exportData() {
            bus.$emit('export-data','Current-Stock-Report-'+moment().format('YYYY-MM-DD'))
        }
    }
}
</script>
