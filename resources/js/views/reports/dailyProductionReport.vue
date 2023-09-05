<template>
    <div class="container-fluid">
        <breadcrumb :options="['Daily Production Report']">
        </breadcrumb>
        <div class="row" style="padding:8px 0px;">
            <div class="col-md-4">
                <button type="button" class="btn btn-success btn-sm" @click="exportData">Export to Excel</button>
            </div>
        </div>
        <advanced-datatable :options="tableOptions">
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
            tableOptions: {
                source: 'report/daily-production',
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
                    // {
                    //     type: 'dropdown',
                    //     title: 'Select Category',
                    //     value: '',
                    //     options: this.categoryOptions
                    // }
                ]
            },
            loading: false,
            cpLoading: false,
        }
    },
    created() {
        let instance = this;
        instance.getData();
    },
    methods: {
        getData() {
            let instance = this;
            this.axiosGet('report/daily-production-supporting-data', function (response) {
                console.log( response.category)
                instance.categoryOptions = response.category;
            }, function (error) {
            });
        },
        changeStatus() {
            this.loading = false
        },
        exportData() {
            bus.$emit('export-data','Daily-Production-Report-'+moment().format('YYYY-MM-DD'))
        }
    }
}
</script>
