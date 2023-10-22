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
                                        <ValidationProvider name="ExpenseHead" mode="eager" rules="required"
                                                            v-slot="{ errors }">
                                            <div class="form-group">
                                                <label for="name">	Expense Head <span class="error">*</span></label>
                                                <input type="text" class="form-control"
                                                       :class="{'error-border': errors[0]}" id="name"
                                                       v-model="Name" name="staff-name" placeholder="Name">
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
                                        <ValidationProvider name="ExpenseType" mode="eager"
                                            rules="required" v-slot="{ errors }">
                                            <div class="form-group">
                                                <label for="status">Balance Type <span class="error">*</span></label>
                                                <select class="form-control" v-model="ExpenseType">
                                                    <option value="Operating">Operating</option>
                                                    <option value="Fixed">Fixed</option>
                                                </select>
                                                <span class="error-message"> {{ errors[0] }}</span>
                                            </div>
                                        </ValidationProvider>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <ValidationProvider name="BF" mode="eager"
                                            rules="required" v-slot="{ errors }">
                                            <div class="form-group">
                                                <label for="status">Balance Forward <span class="error">*</span></label>
                                                <select class="form-control" v-model="bf">
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

export default {
    mixins: [Common],
    data() {
        return {
            title: '',
            UserId: '',
            locationCode: '',
            headCode:'',
            status: 'Y',
            bf: 'Y',
            type: 'add',
            actionType: '',
            buttonShow: false,
            roles: [],
            allSubMenu: [],
            allSubMenuId: [],
        }
    },
    computed: {},
    mounted() {
        $('#add-edit-dept').on('hidden.bs.modal', () => {
            this.$emit('changeStatus')
        });
        bus.$on('add-edit-expense-head', (row) => {
            if (row) {
                this.selectedBusiness = [];
                this.selectedDepartment = [];
                let instance = this;
                console.log(row.Id)
                this.axiosGet('setup/get-expense-head-info/' + row.HeadCode, function (response) {
                    var data = response.data;
                    instance.title = 'Update Expense Head';
                    instance.buttonText = "Update";
                    instance.headCode = data.HeadCode;
                    instance.Name = data.ExpenseHead;
                    instance.status = data.Active;
                    instance.bf = data.BF;
                    instance.ExpenseType = data.ExpenseType;
                    instance.buttonShow = true;
                    instance.actionType = 'edit';
                }, function (error) {

                });
            } else {
                this.title = 'Add Expense Head';
                this.buttonText = "Add";
                this.UserId = '';
                this.Name = '';
                this.Address = '',
                this.NID = '',
                this.mobile = '',
                this.email = '',
                this.status = '',
                this.bf = '',
                this.ExpenseType = '',
                this.password = '',
                this.confirm = '',
                this.userType = '',
                this.allSubMenu = [];
                this.buttonShow = true;
                this.actionType = 'add'
            }
            $("#add-edit-dept").modal("toggle");
            // $(".error-message").html("");
        })
    },
    destroyed() {
        bus.$off('add-edit-expense-head')
    },
    methods: {
        closeModal() {
            $("#add-edit-dept").modal("toggle");
        },
        onSubmit() {
            this.$store.commit('submitButtonLoadingStatus', true);
            let url = '';
            if (this.actionType === 'add') url = 'setup/expense-head-add';
            else url = 'setup/expense-head-update'
            this.axiosPost(url, {
                HeadCode: this.headCode,
                Name: this.Name,
                status: this.status,
                bf: this.bf,
                ExpenseType: this.ExpenseType,
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
