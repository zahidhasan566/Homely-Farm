<template>
    <div class="container-fluid">
        <breadcrumb :options="['Daily Purchase']">
        </breadcrumb>
        <div class="row" style="padding:8px 0px;">
            <div class="col-md-4">
                <button type="button" class="btn btn-success btn-sm" @click="exportData">Export to Excel</button>
            </div>
        </div>
        <advanced-datatable :options="tableOptions" v-if="showTable">

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
            showTable:false,
            tableOptions: {
            },
            loading: false,
            cpLoading: false,
        }
    },
    mounted() {
        let instance = this;
        instance.getData();
    },

    methods: {
        changeStatus() {
            this.loading = false
        },
        getData() {
            let instance = this;
            this.axiosGet('report/daily-production-supporting-data', function (response) {
                instance.loadDatatable(response)
            }, function (error) {
            });
        },
        loadDatatable(response) {
            this.showTable = true
            this.tableOptions = {
                source: 'report/daily-purchase-report',
                search: true,
                slots: [10],
                // hideColumn: ['CreatedAt'],
                sortable: [2],
                pages: [20, 50, 100],
                showFilter: ['CategoryCode'],
                // colSize: ['col-lg-1','col-lg-1','col-lg-1','col-lg-1','col-lg-2','col-lg-2','col-lg-2','col-lg-2'],
                filters: [
                    {
                        type: 'rangepicker',
                        value: [moment().format('DD-MM-YYYY'),moment().format('DD-MM-YYYY')]
                    },
                    {
                        type: 'dropdown',
                        title: 'Select Category',
                        value: '',
                        options: response.category
                    }
                ]
            }
        },
        exportData() {
            bus.$emit('export-data','Expense-Report-'+moment().format('YYYY-MM-DD'))
        }
    }
}
</script>
