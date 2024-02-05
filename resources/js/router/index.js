import Vue from 'vue'
import VueRouter from 'vue-router';
import Main from '../components/layouts/Main';
import Dashboard from '../views/dashboard/Index.vue';
import Login from '../views/auth/Login.vue';
import {baseurl} from '../base_url'
import NotFound from '../views/404/Index';

import Users from '../views/users/Index';
import production from "../views/production/index.vue";
import purchase from "../views/purchase/index.vue";
import sales from "../views/sales/index.vue";
import customers from "../views/customers/index.vue";
import dailyProductionReport from "../views/reports/dailyProductionReport.vue";
import dailySalesReport from "../views/reports/dailySalesReport.vue";
import getVaccineReport from "../views/reports/getVaccineReport.vue";
import customerWiseSales from "../views/reports/customerWiseSales.vue";
import currentStockReport from "../views/reports/currentStockReport.vue";
import reportItemBalance from "../views/reports/itemBalanceReport.vue";
import reportExpenses from "../views/reports/reportExpenses.vue";
import locationWisePl from "../views/reports/locationWisePl.vue";
import dailyPurchase from "../views/reports/dailyPurchase.vue";
import categoryIndex from "../views/setup/CategoryIndex.vue";
import locationIndex from "../views/setup/LocationIndex.vue";
import categoryLocationIndex from "../views/setup/CategoryLocationIndex.vue";
import expenseHeadIndex from "../views/setup/ExpenseHeadIndex.vue";
import expense from "../views/expense/index.vue";
import monthClosing from "../views/setup/monthClosing.vue";
import VaccineSchedule from "../views/vaccineschedule/VaccineSchedule.vue";
<<<<<<< HEAD
import Transfer from "../views/transfer/Index.vue";

=======
import itemListIndex from "../views/setup/ItemIndex.vue";
import Transfer from "../views/transfer/Index.vue";
>>>>>>> 5cc3b19939d68cbb3f36fc1c5e246f44b2791e04
Vue.use(VueRouter);


const config = () => {
    let token = localStorage.getItem('token');
    return {
        headers: {Authorization: `Bearer ${token}`}
    };
}
const checkToken = (to, from, next) => {
    let token = localStorage.getItem('token');
    if (token === undefined || token === null || token === '') {
        next(baseurl + 'login');
    } else {
        next();
    }
};

const activeToken = (to, from, next) => {
    let token = localStorage.getItem('token');
    if (token === 'undefined' || token === null || token === '') {
        next();
    } else {
        next(baseurl);
    }
};

const routes = [
    {
        path: baseurl,
        component: Main,
        redirect: {name: 'Dashboard'},
        children: [
            //COMMON ROUTE | SHOW DASHBOARD DATA
            {
                path: baseurl + 'dashboard',
                name: 'Dashboard',
                component: Dashboard
            },
            //ADMIN ROUTE | SHOW USER LIST
            {
                path: baseurl + 'users',
                name: 'Users',
                component: Users
            },
            //PRODUCTION ROUTE
            {
                path: baseurl + 'production/productionList',
                name: 'production',
                component: production
            },
            //PURCHASE   ROUTE
            {
                path: baseurl + 'purchase/purchaseList',
                name: 'purchase',
                component: purchase
            },
            //SALES   ROUTE
            {
                path: baseurl + 'sales/salesList',
                name: 'sales',
                component: sales
            },
            //EXPENSE   ROUTE
            {
                path: baseurl + 'expense/expenseList',
                name: 'expense',
                component: expense
            },


            //Setup Customer   ROUTE
            {
                path: baseurl + 'customer/customerList',
                name: 'customer',
                component: customers
            },
            {
                path: baseurl + 'setup/category',
                name: 'category',
                component: categoryIndex
            },
            {
                path: baseurl + 'setup/location',
                name: 'location',
                component: locationIndex
            },
            {
                path: baseurl + 'setup/category-location',
                name: 'categoryLocation',
                component: categoryLocationIndex
            },
            {
                path: baseurl + 'setup/expense-head',
                name: 'expenseHeadIndex',
                component: expenseHeadIndex
            },
			{
                path: baseurl + 'setup/itemList',
                name: 'itemListIndex',
                component: itemListIndex
            },


            //Report
            {
                path: baseurl + 'report/dailyProduction',
                name: 'dailyProductionReport',
                component: dailyProductionReport
            },
            {
                path: baseurl + 'report/dailySales',
                name: 'dailySalesReport',
                component: dailySalesReport
            },
            {
                path: baseurl + 'report/vaccine-report',
                name: 'getVaccineReport',
                component: getVaccineReport
            },
            {
                path: baseurl + 'report/current-stock',
                name: 'currentStockReport',
                component: currentStockReport
            },
            {
                path: baseurl + 'report/itembalance',
                name: 'reportItemBalance',
                component: reportItemBalance
            },
            {
                path: baseurl + 'report/expensesreport',
                name: 'reportExpenses',
                component: reportExpenses
            },
            {
                path: baseurl + 'report/locationwisepl',
                name: 'locationWisePl',
                component: locationWisePl
            },
            {
                path: baseurl + 'report/customer-wise-sales',
                name: 'customerWiseSales',
                component: customerWiseSales
            },
            {
                path: baseurl + 'report/daily-purchase-report',
                name: 'dailyPurchase',
                component: dailyPurchase
            },
            {
                path: baseurl + 'setup/closing-report',
                name: 'monthClosing',
                component: monthClosing
            },
            {
                path: baseurl + 'vaccineschedule/list',
                name: 'VaccineSchedule',
                component: VaccineSchedule
            },
            {
                path: baseurl + 'transfer/transferList',
                name: 'Transfer',
                component: Transfer
            },
        ],
        beforeEnter(to, from, next) {
            checkToken(to, from, next);
        }
    },
    {
        path: baseurl + 'login',
        name: 'Login',
        component: Login,
        beforeEnter(to, from, next) {
            activeToken(to, from, next);
        }
    },
    {
        path: baseurl + '*',
        name: 'NotFound',
        component: NotFound,
    },
]
const router = new VueRouter({
    mode: 'history',
    base: process.env.baseurl,
    routes
});

router.afterEach(() => {
    $('#preloader').hide();
});

export default router
