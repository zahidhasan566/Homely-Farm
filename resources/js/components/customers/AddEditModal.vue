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
                                        <ValidationProvider name="Name" mode="eager" rules="required"
                                                            v-slot="{ errors }">
                                            <div class="form-group">
                                                <label for="name">Name <span class="error">*</span></label>
                                                <input type="text" class="form-control"
                                                       :class="{'error-border': errors[0]}" id="name"
                                                       v-model="Name" name="staff-name" placeholder="Name">
                                                <span class="error-message"> {{ errors[0] }}</span>
                                            </div>
                                        </ValidationProvider>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <ValidationProvider name="Address" mode="eager" rules="required"
                                                            v-slot="{ errors }">
                                            <div class="form-group">
                                                <label for="name">Address <span class="error">*</span></label>
                                                <input type="text" class="form-control"
                                                       :class="{'error-border': errors[0]}" id="Address"
                                                       v-model="Address" name="Address" placeholder="Address">
                                                <span class="error-message"> {{ errors[0] }}</span>
                                            </div>
                                        </ValidationProvider>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <ValidationProvider name="Status" mode="eager" rules="required"
                                                            v-slot="{ errors }">
                                            <div class="form-group">
                                                <label for="status">Status <span class="error">*</span></label>
                                                <select class="form-control" v-model="status">
                                                    <option value="Y">Active</option>
                                                    <option value="N">Inactive</option>
                                                </select>
                                                <span class="error-message"> {{ errors[0] }}</span>
                                            </div>
                                        </ValidationProvider>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <ValidationProvider name="Address" mode="eager" rules=""
                                                            v-slot="{ errors }">
                                            <div class="form-group">
                                                <label for="name">Name In Bengali </label>
                                                <input type="text" class="form-control"
                                                       :class="{'error-border': errors[0]}" id="Address"
                                                       v-model="nameInBengali" name="Address" placeholder="Address">
                                                <span class="error-message"> {{ errors[0] }}</span>
                                            </div>
                                        </ValidationProvider>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <ValidationProvider name="Address" mode="eager" rules=""
                                                            v-slot="{ errors }">
                                            <div class="form-group">
                                                <label for="name">Address In Bengali</label>
                                                <input type="text" class="form-control"
                                                       :class="{'error-border': errors[0]}" id="Address"
                                                       v-model="addressInBengali" name="Address" placeholder="Address">
                                                <span class="error-message"> {{ errors[0] }}</span>
                                            </div>
                                        </ValidationProvider>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <ValidationProvider name="Address" mode="eager" rules=""
                                                            v-slot="{ errors }">
                                            <div class="form-group">
                                                <label for="name">Mobile </label>
                                                <input type="text" class="form-control"
                                                       :class="{'error-border': errors[0]}" id="Address"
                                                       v-model="mobile" name="Address" placeholder="Address">
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
            roles: [],
            allSubMenu: [],
            allSubMenuId: [],
            nameInBengali: '',
            addressInBengali: '',
        }
    },
    computed: {},
    mounted() {
        $('#add-edit-dept').on('hidden.bs.modal', () => {
            this.$emit('changeStatus')
        });
        bus.$on('add-edit-customers', (row) => {
            if (row) {
                this.selectedBusiness = [];
                this.selectedDepartment = [];
                let instance = this;
                console.log(row.Id)
                this.axiosGet('customers/get-customer-info/' + row.CustomerCode, function (response) {
                    var user = response.data;
                    instance.title = 'Update Customer';
                    instance.buttonText = "Update";
                    instance.customerCode = user.CustomerCode;
                    instance.Name = user.CustomerName;
                    instance.Address = user.Address;
                    instance.status = user.Active;
                    instance.nameInBengali = user.CustomerNameBangla;
                    instance.addressInBengali = user.AddressBangla;
                    instance.mobile = user.MobileNo;
                    instance.buttonShow = true;
                    instance.actionType = 'edit';
                    instance.getData();
                }, function (error) {

                });
            } else {
                this.title = 'Add Customer';
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
        bus.$off('add-edit-customers')
    },
    methods: {
        closeModal() {
            $("#add-edit-dept").modal("toggle");
        },
        getData() {
            let instance = this;
            this.axiosGet('user/modal', function (response) {
                instance.roles = response.roles;
                instance.allSubMenu = response.allSubMenus;
            }, function (error) {

            });
        },
        onSubmit() {
            this.$store.commit('submitButtonLoadingStatus', true);
            let url = '';
            if (this.actionType === 'add') url = 'customers/add';
            else url = 'customers/update'
            this.axiosPost(url, {
                CustomerCode: this.customerCode,
                Name: this.Name,
                email: this.email,
                mobile: this.mobile,
                status: this.status,
                NID: this.NID,
                Address: this.Address,
                userType: this.userType,
                password: this.password,
                nameInBengali: this.nameInBengali,
                addressInBengali: this.addressInBengali,
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
