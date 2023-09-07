<template>
  <div class="container-fluid">
    <breadcrumb :options="['Location']">
      <button class="btn btn-primary" @click="addUserModal()">Add Location</button>
    </breadcrumb>
    <div class="row" style="padding:8px 0px;">
      <div class="col-md-4">
        <button type="button" class="btn btn-success btn-sm" @click="exportData">Export to Excel</button>
      </div>
    </div>
    <advanced-datatable :options="tableOptions">
        <template slot="status" slot-scope="row">
            <span v-if="row.item.Active==='Y'">Active</span>
            <span v-else>Inactive</span>
        </template>
      <template slot="action" slot-scope="row">
        <a href="javascript:" @click="addUserModal(row.item)"> <i class="ti-pencil-alt">Edit</i></a>
<!--        <a href="javascript:" @click="changePassword(row.item.UserId)"> <i class="ti-lock"></i></a>-->
      </template>

    </advanced-datatable>
    <add-edit-location @changeStatus="changeStatus" v-if="loading"/>
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
        source: 'setup/location-list',
        search: true,
        slots: [2,3],
        //hideColumn: ['RoleID','UserId'],
        slotsName: ['status','action'],
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
  methods: {
    changeStatus() {
      this.loading = false
    },
      addUserModal(row = '') {
      this.loading = true;
      setTimeout(() => {
        bus.$emit('add-edit-location', row);
      })
    },
    exportData() {
      bus.$emit('export-data','location-list-'+moment().format('YYYY-MM-DD'))
    }
  }
}
</script>
