<template>
    <div>
        <div class="modal fade" id="add-edit-dept" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
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
                                        <ValidationProvider name="CategoryName" mode="eager" rules="required"
                                                            v-slot="{ errors }">
                                            <label for="CategoryName">Category Name <span class="error">*</span></label>
                                            <select class="form-control" :class="{'error-border': errors[0]}"
                                                    v-model="CategoryCode" name="item">
                                                <option value="">
                                                    Select category
                                                </option>
                                                <option v-for="(item,index) in category"
                                                        :key="index"
                                                        :value="item.CategoryCode">
                                                    {{ item.CategoryName }}
                                                </option>
                                            </select>
                                        </ValidationProvider>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <ValidationProvider name="LocationName" mode="eager" rules="required"
                                                            v-slot="{ errors }">
                                        <label for="LocationName">Location Name <span class="error">*</span></label>
                                        <select class="form-control" :class="{'error-border': errors[0]}"
                                                v-model="LocationCode" name="item">
                                            <option value="">
                                                Select Location
                                            </option>
                                            <option v-for="(item,index) in location"
                                                    :key="index"
                                                    :value="item.LocationCode">
                                                {{ item.LocationName }}
                                            </option>
                                        </select>
                                        </ValidationProvider>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <ValidationProvider name="Status" mode="eager" rules="required"
                                                            v-slot="{ errors }">
                                            <div class="form-group" style="padding-top:20px">
                                                <label for="status">Status <span class="error">*</span></label>
                                                <select class="form-control" v-model="status">
                                                    <option value="Y">Active</option>
                                                    <option value="N">Inactive</option>
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

export default {
    mixins: [Common],
    data() {
        return {
            title: '',
            UserId: '',
            customerCode: '',
            Name: '',
            Address: '',
            NID: '',
            buttonText: '',
            mobile: '',
            email: '',
            status: '',
            password: '',
            confirm: '',
            userType: '',
            type: 'add',
            actionType: '',
            buttonShow: false,
            category:[],
            location:[],
            roles: [],
            allSubMenu: [],
            allSubMenuId: [],
            CategoryCode:'',
            LocationCode:'',
        }
    },
    computed: {},
    mounted() {
        $('#add-edit-dept').on('hidden.bs.modal', () => {
            this.$emit('changeStatus')
        });
        bus.$on('add-edit-category-location', (row) => {
            if (row) {
                this.selectedBusiness = [];
                this.selectedDepartment = [];
                let instance = this;

                this.axiosGet('setup/get-category-location-info/'+row.CategoryCode+'/'+ row.LocationCode, function (response) {
                    console.log(response)
                    var data = response.data;
                    instance.title = 'Update Category Location';
                    instance.buttonText = "Update";

                    instance.CategoryCode=data.CategoryCode
                    instance.LocationCode = data.LocationCode
                    instance.status = data.Active;

                    instance.buttonShow = true;
                    instance.actionType = 'edit';
                    instance.getData();
                }, function (error) {

                });
            } else {
                this.title = 'Add Category Location';
                this.buttonText = "Add";
                this.UserId = '';
                this.Name = '';
                this.Address = '',
                    this.NID = '',
                    this.mobile = '',
                    this.email = '',
                    this.status = '',
                    this.password = '',
                    this.confirm = '',
                    this.userType = '',
                    this.allSubMenu = [];
                this.buttonShow = true;
                this.actionType = 'add'
                this.getData();
            }
            $("#add-edit-dept").modal("toggle");
            // $(".error-message").html("");
        })
    },
    destroyed() {
        bus.$off('add-edit-category-location')
    },
    methods: {
        closeModal() {
            $("#add-edit-dept").modal("toggle");
        },
        getData() {
            let instance = this;
            this.axiosGet('setup/get-category-location-supporting-data', function (response) {
                instance.category =  response.category
                instance.location =  response.location
            }, function (error) {

            });
        },
        onSubmit() {
            this.$store.commit('submitButtonLoadingStatus', true);
            let url = '';
            if (this.actionType === 'add') url = 'setup/category-location-add';
            else url = 'setup/category-location-update'
            this.axiosPost(url, {
                CategoryCode: this.CategoryCode,
                LocationCode : this.LocationCode,
                status: this.status,

            }, (response) => {
                this.successNoti(response.message);
                $("#add-edit-dept").modal("toggle");
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
