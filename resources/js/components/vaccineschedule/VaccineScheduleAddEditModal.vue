<template>
    <div>
        <div class="modal fade" id="add-edit-vaccineschedule" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="modal-title modal-title-font" id="exampleModalLabel">{{ title }}</div>
                    </div>

                    <ValidationObserver v-slot="{ handleSubmit }">
                        <form class="form-horizontal" id="form" @submit.prevent="handleSubmit(onSubmit)">
                            <div class="modal-body">
                                <div class="row">

                                    <div class="col-12 col-md-6">
                                        <ValidationProvider name="ScheduleDate" mode="eager" rules="required"
                                                            v-slot="{ errors }">
                                            <div class="form-group">
                                                <label for="name">Schedule Date <span class="error">*</span></label>
                                                <input type="date" class="form-control"
                                                       :class="{'error-border': errors[0]}"
                                                       v-model="ScheduleDate" placeholder="Schedule Date">
                                                <span class="error-message"> {{ errors[0] }}</span>
                                            </div>
                                        </ValidationProvider>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <ValidationProvider name="VaccineName" mode="eager" rules="required"
                                                            v-slot="{ errors }">
                                            <div class="form-group">
                                                <label for="name">Vaccine Name <span class="error">*</span></label>
                                                <select class="form-control" v-model="VaccineName">
                                                    <option value="">Select Option</option>
                                                    <option :value="item.ItemCode" v-for="(item , index) in items" :key="index">{{ item.ItemName }}</option>
                                                </select>
                                                <span class="error-message"> {{ errors[0] }}</span>
                                            </div>
                                        </ValidationProvider>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <ValidationProvider name="UnitPrice" mode="eager" rules="required"
                                                            v-slot="{ errors }">
                                            <div class="form-group">
                                                <label for="name">Total Price <span class="error">*</span></label>
                                                <input type="number" class="form-control"
                                                       :class="{'error-border': errors[0]}" id="UnitPrice"
                                                       v-model="UnitPrice"  placeholder="Unit Price">
                                                <span class="error-message"> {{ errors[0] }}</span>
                                            </div>
                                        </ValidationProvider>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <ValidationProvider name="CategoryCode" mode="eager" rules="required"
                                                            v-slot="{ errors }">
                                            <div class="form-group">
                                                <label for="CategoryCode">CategoryCode <span class="error">*</span></label>
                                                <select class="form-control" v-model="CategoryCode"
                                                    @change="getLocationData()">
                                                    <option value=""></option>
                                                    <option :value="CategoryList.CategoryCode" v-for="(CategoryList , index) in CategoryList" :key="index">{{ CategoryList.CategoryName }}</option>
                                                </select>
                                                <span class="error-message"> {{ errors[0] }}</span>
                                            </div>
                                        </ValidationProvider>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <ValidationProvider name="LocationCode" mode="eager" rules="required"
                                                            v-slot="{ errors }">
                                            <div class="form-group">
                                                <label for="Location">LocationCode <span class="error">*</span></label>
                                                <select class="form-control" v-model="LocationCode">
                                                    <option value=""></option>
                                                    <option :value="LocationList.LocationCode" v-for="(LocationList , index) in LocationList" :key="index">{{ LocationList.LocationName }}</option>
                                                </select>
                                                <span class="error-message"> {{ errors[0] }}</span>
                                            </div>
                                        </ValidationProvider>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <ValidationProvider name="NextScheduleDate" mode="eager"
                                                            v-slot="{ errors }">
                                            <div class="form-group">
                                                <label for="date">NextScheduleDate</label>
                                                <input type="date" class="form-control"
                                                        :class="{'error-border': errors[0]}"
                                                        v-model="NextScheduleDate" placeholder="Next Schedule Date">
                                                <span class="error-message"> {{ errors[0] }}</span>
                                            </div>
                                        </ValidationProvider>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <ValidationProvider name="Expense" mode="eager" rules="required"
                                                            v-slot="{ errors }">
                                            <div class="form-group">
                                                <label for="name">Expense <span class="error">*</span></label>
                                                <select class="form-control" v-model="expense">
                                                    <option value="">Select Option</option>
                                                    <option value="Y">Yes</option>
                                                    <option value="N">No</option>
                                                </select>
                                                <span class="error-message"> {{ errors[0] }}</span>
                                            </div>
                                        </ValidationProvider>
                                    </div>

                                </div>
                            </div>
                            <div class="modal-footer">
                                <submit-form v-if="buttonShow" :name="buttonText"/>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" @click="closeModal">Close</button>
                            </div>
                        </form>
                    </ValidationObserver>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import {bus} from "../../app";
import {Common} from "../../mixins/common";
import {mapGetters} from "vuex";
import moment from "moment"

export default {
    mixins: [Common],
    data() {
        return {
            title: '',
            UserId: '',
            ScheduleDate: '',
            ScheduleCode: '',
            VaccineName: '',
            UnitPrice: '',
            CategoryCode: '',
            CategoryList: [],
            LocationCode: '',
            LocationList: [],
            items:[],
            NextScheduleDate: '',
            type: 'add',
            actionType: '',
            buttonShow: false,
            roles: [],
            allSubMenu: [],
            allSubMenuId: [],
            expense:''
        }
    },
    computed: {},
    mounted() {
        $('#add-edit-vaccineschedule').on('hidden.bs.modal', () => {
            this.$emit('changeStatus')
        });
        bus.$on('add-edit-vaccineschedule', (row) => {
            if (row) {
                console.log(row,'roe')
                let instance = this;
                this.getData();
                instance.title = 'Update Category';
                instance.buttonText = "Update";
                instance.ScheduleDate = row.ScheduleDate.substring(0,10);
                instance.VaccineName = row.ItemCode;
                instance.UnitPrice = row.UnitPrice;
                instance.CategoryCode = row.CategoryCode;
                instance.expense = row.Expense;
                this.getLocationData();
                instance.LocationCode = row.LocationCode;
                if(row.NextScheduleDate){
                    instance.NextScheduleDate = row.NextScheduleDate.substring(0,10);
                }
                instance.ScheduleCode = row.ScheduleCode;
                instance.buttonShow = true;
                instance.actionType = 'edit';
            } else {
                this.title = 'Add Vaccine Schedule';
                this.buttonText = "Add";
                this.ScheduleDate = moment().format('yyyy-MM-DD');
                this.VaccineName = "";
                this.UnitPrice = "";
                this.CategoryCode = "";
                this.LocationCode = "";
                this.NextScheduleDate= ""
                this.CategoryList = [];
                this.allSubMenu = [];
                this.buttonShow = true;
                this.actionType = 'add'
                this.getData();

            }
            $("#add-edit-vaccineschedule").modal("toggle");
        })
    },
    destroyed() {
        bus.$off('add-edit-vaccineschedule')
    },
    methods: {
        closeModal() {
            $("#add-edit-vaccineschedule").modal("toggle");
        },
        getLocationData(){
            let instance = this;
            let CategoryCodeValue = instance.CategoryCode;
            let url = 'production/category-wise-item';
            this.axiosPost(url, {
                CategoryCode:CategoryCodeValue ,
            }, (response) => {
                instance.LocationList = response.locations;
            }, (error) => {
                this.errorNoti(error);
            })
        },
        getData() {
            let instance = this;
            this.axiosGet('user/modal', function (response) {
                instance.roles = response.roles;
                instance.allSubMenu = response.allSubMenus;
            }, function (error) {

            });
            this.axiosGet('vaccineschedule/supporting-data', function (response) {
                instance.CategoryList = response.category;
                instance.items = response.items;
            }, function (error) {
            });

        },
        onSubmit() {
            this.$store.commit('submitButtonLoadingStatus', true);
            let url = '';
            url = 'vaccineschedule/add';
            this.axiosPost(url, {
                ScheduleDate: this.ScheduleDate,
                VaccineName: this.VaccineName,
                UnitPrice: this.UnitPrice,
                CategoryCode: this.CategoryCode,
                LocationCode: this.LocationCode,
                NextScheduleDate: this.NextScheduleDate,
                ActionType: this.actionType,
                ScheduleCode: this.ScheduleCode,
                expense: this.expense
            }, (response) => {
                this.successNoti(response.message);
                $("#add-edit-vaccineschedule").modal("toggle");
                bus.$emit('refresh-datatable');
                this.$store.commit('submitButtonLoadingStatus', false);
            }, (error) => {
                this.errorNoti(error);
                this.$store.commit('submitButtonLoadingStatus', false);
            })
        }
    }
}
</script>

<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>
