<template>
    <div>
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Dashboard</h4>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item active">Welcome to Homely Farm Dashboard</li>
                </ol>
            </div>
            <div class="col-sm-6">
                <div class="float-right d-none d-md-block">
                    <div class="dropdown">
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="#">Action</a> <a class="dropdown-item" href="#">Another action</a> <a class="dropdown-item" href="#">Something else here</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="#">Separated link</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card mini-stat bg-primary text-white">
                <div class="card-body">
                    <div class="mb-4">
                        <div class="float-left mini-stat-img mr-4"><img src="assets/images/services-icon/01.png" alt="" /></div>
                        <h5 class="font-16 text-uppercase mt-0 text-white-50">Purchase </h5>
                        <table class="table table-striped" v-if="purchase.length>0">
                            <thead>
                            <tr>
                                <th>Category Name</th>
                                <th>Value</th>
                            </tr>
                            </thead>
                            <tbody id="tagModal-body">
                            <tr v-for="(singlePurchase,index) in purchase">
                                <td>{{singlePurchase.CategoryName}}</td>
                                <td>{{singlePurchase.Value}}</td>
                            </tr>

                            </tbody>
                        </table>
                        <h4 v-else class="font-500"> No Data to Show</h4>
                    </div>
                    <div class="pt-2">
                        <div class="float-right">
                            <router-link  :to="{ path: 'purchase/purchaseList'}">
                                <i  style="color: white" class="mdi mdi-arrow-right h5"></i>
                            </router-link>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card mini-stat bg-primary text-white">
                <div class="card-body">
                    <div class="mb-4">
                        <div class="float-left mini-stat-img mr-4"><img src="assets/images/services-icon/02.png" alt="" /></div>
                        <h5 class="font-16 text-uppercase mt-0 text-white-50">Production</h5>
                        <table class="table" v-if="production.length>0">
                            <thead>
                            <tr>
                                <th>Category Name</th>
                                <th>Quantity</th>
                            </tr>
                            </thead>
                            <tbody id="tagModal-body">
                            <tr class="trClass" v-for="(singleProduction,index) in production">
                                <td >{{singleProduction.CategoryName}}</td>
                                <td>{{singleProduction.Quantity}}</td>
                            </tr>

                            </tbody>
                        </table>
                        <h4 v-else class="font-500"> No Data to Show</h4>
                    </div>
                    <div class="pt-2">
                        <div class="float-right">
                            <router-link  :to="{ path: 'production/productionList'}">
                                <i  style="color: white" class="mdi mdi-arrow-right h5"></i>
                            </router-link>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card mini-stat bg-primary text-white">
                <div class="card-body">
                    <div class="mb-4">
                        <div class="float-left mini-stat-img mr-4"><img src="assets/images/services-icon/03.png" alt="" /></div>
                        <h5 class="font-16 text-uppercase mt-0 text-white-50">Sales</h5>
                        <table class="table"  v-if="sales.length>0">
                            <thead>
                            <tr>
                                <th>Category Name</th>
                                <th>Amount</th>
                            </tr>
                            </thead>
                            <tbody id="tagModal-body">
                            <tr class="trClass" v-for="(singleSales,index) in sales">
                                <td >{{singleSales.CategoryName}}</td>
                                <td>{{singleSales.Amount}}</td>
                            </tr>

                            </tbody>
                        </table>
                        <h4 v-else class="font-500"> No Data to Show</h4>
                    </div>
                    <div class="pt-2">
                        <div class="float-right">
                            <router-link  :to="{ path: 'sales/salesList'}">
                                <i  style="color: white" class="mdi mdi-arrow-right h5"></i>
                            </router-link>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card mini-stat bg-primary text-white">
                <div class="card-body">
                    <div class="mb-4">
                        <div class="float-left mini-stat-img mr-4"><img src="assets/images/services-icon/04.png" alt="" /></div>
                        <h5 class="font-16 text-uppercase mt-0 text-white-50">Expense</h5>
                        <table class="table" v-if="expense.length>0">
                            <thead>
                            <tr>
                                <th>Category Name</th>
                                <th>Amount</th>
                            </tr>
                            </thead>
                            <tbody id="tagModal-body">
                            <tr class="trClass" v-for="(singleExpense,index) in expense">
                                <td >{{singleExpense.CategoryName}}</td>
                                <td>{{singleExpense.Amount}}</td>
                            </tr>

                            </tbody>
                        </table>
                        <h4 v-else class="font-500"> No Data to Show</h4>
                    </div>
                    <div class="pt-2">
                        <div class="float-right">
                            <router-link  :to="{ path: 'expense/expenseList'}">
                                <i  style="color: white" class="mdi mdi-arrow-right h5"></i>
                            </router-link>

                        </div>
<!--                        <p class="text-white-50 mb-0">Since last month</p>-->
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</template>
<script>
import {Common} from "../../mixins/common";

export default {
  mixins: [Common],
  data() {
    return {
      isLoading: true,
      purchase:[],
      production:[],
      sales:[],
      expense:[],
    }
  },
  computed: {
    me() {
      return this.$store.state.me
    }
  },
  created() {
    this.getData();
  },
  methods: {
    getData() {
      this.axiosGet('dashboard-data', (response) => {
         this.purchase = response.data[0];
         this.production =  response.data[1];
         this.sales = response.data[2];
         this.expense = response.data[3];
          console.log(this.sales)

      this.isLoading = false;
      }, (error) => {
        this.errorNoti(error);
      });
    }
  }
}
</script>


<style scoped>
.bg-blue {
  background: #626ed4!important;
  text-align: center;
  text-transform: uppercase;
}
.card-body {
  padding: 0.60rem !important;
}
.bg-blue span {
  font-size: 18px;
}
.trClass{
    color: #ffffff;
}
.task {
  background: #00a55d2b;
  padding: 5px 8px;
  font-size: 14px;
  margin-bottom: 10px;
  border-radius: 5px;
  font-weight: bold;
}
.links {
  /*height: 148px;*/
}
.helpline {
  height: 120px;
  text-align: center;
  position: relative;
  background-image: linear-gradient(146deg,#626ed4,#626ed4);
}
.helpline span {
  position: absolute;
  top: 40%;
  left: 0;
  right: 0;
  font-weight: bold;
  font-size: 20px;
  text-transform: uppercase;
  color: #FFFFFF;
}
.contact {
  height: 120px;
  text-align: center;
  padding-top: 20px;
  background-image: linear-gradient(146deg,#626ed4,#626ed4);
}
.contact p {
  margin: 0;
  padding: 2px;
  font-weight: bold;
  color: #ffffff;
}
.header-bg {
  text-align: center;
  font-size: 16px;
  font-weight: bold;
  text-transform: uppercase;
}
</style>
