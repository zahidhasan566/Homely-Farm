<template>
    <div>
        <div class="modal fade" id="add-edit-dept" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="modal-title modal-title-font" id="exampleModalLabel">{{ title }}</div>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" @click="closeModal">
                            Close
                        </button>
                    </div>
                    <ValidationObserver v-slot="{ handleSubmit }">
                        <form class="form-horizontal" id="formProduction" @submit.prevent="handleSubmit(onSubmit)">
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-12 col-md-6">
                                        <ValidationProvider name="production_date" mode="eager"
                                                            v-slot="{ errors }">
                                            <div class="form-group">
                                                <label for="name">Production Date <span class="error">*</span></label>
                                                <datepicker v-model="production_date" :dayStr="dayStr"
                                                            placeholder="YYYY-MM-DD" :firstDayOfWeek="0"/>
                                                <span class="error-message"> {{ errors[0] }}</span>
                                            </div>
                                        </ValidationProvider>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <ValidationProvider name="Reference" mode="eager" rules="required"
                                                            v-slot="{ errors }">
                                            <div class="form-group">
                                                <label for="name">Reference <span class="error">*</span></label>
                                                <input type="text" class="form-control"
                                                       :class="{'error-border': errors[0]}" id="Reference"
                                                       v-model="reference" name="staff-name" placeholder="Reference">
                                                <span class="error-message"> {{ errors[0] }}</span>
                                            </div>
                                        </ValidationProvider>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <ValidationProvider name="UserType" mode="eager" rules="required"
                                                            v-slot="{ errors }">
                                            <div class="form-group">
                                                <label for="user-type">Category <span class="error">*</span></label>
                                                <multiselect v-model="categoryType" :options="category" v-if="actionType==='add'"
                                                             :multiple="false"
                                                             @input="getItemByCategory"
                                                             :close-on-select="true"
                                                             :clear-on-select="false" :preserve-search="true"
                                                             placeholder="Select Category"
                                                             label="CategoryName" track-by="CategoryCode">

                                                </multiselect>
                                                <multiselect v-model="categoryType" :options="category" v-else
                                                             :multiple="false"
                                                             :disabled="true"
                                                             :close-on-select="true"
                                                             :clear-on-select="false" :preserve-search="false"
                                                             placeholder="Select Category"
                                                             label="CategoryName" track-by="CategoryCode">

                                                </multiselect>
                                                <span class="error-message"> {{ errors[0] }}</span>
                                            </div>
                                        </ValidationProvider>

                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div style="padding: 32px 0">
                                            <input type="radio" v-model="returnVal" value="Y"> Return
                                        </div>

                                    </div>

                                </div>
                            </div>
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3" style="margin-bottom: 10px;">
                                            <label for="payment-required-by">Add production</label>
                                        </div>
                                        <div class="offset-md-5 col-md-4">
                                            <button style="float: right;" id="add-row" type="button"
                                                    class="btn btn-success btn-sm" @click="addRow">Add Row
                                            </button>
                                        </div>
                                    </div>
                                    <div class="table-condensed">
                                        <table
                                            class="table table-bordered table-striped dt-responsive nowrap dataTable no-footer dtr-inline table-sm">
                                            <thead class="thead-dark">
                                            <tr>
                                                <th>Item <span class="required-field">*</span></th>
                                                <th>Item Code<span class="required-field">*</span></th>
                                                <th>Location <span class="required-field">*</span></th>
                                                <th>Quantity<span class="required-field">*</span></th>
                                                <th>Value<span class="required-field">*</span></th>
                                                <th>Action</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr v-for="(field,index) in fields" :key="index">
                                                <td>
                                                    <multiselect v-model="field.item" :options="items"
                                                                 :multiple="false"
                                                                 @input="setItemCode(index)"
                                                                 :close-on-select="true"
                                                                 :clear-on-select="false" :preserve-search="true"
                                                                 placeholder="Select Category"
                                                                 label="ItemName" track-by="ItemCode">

                                                    </multiselect>
                                                    <span class="error"
                                                          v-if="errors[index] !== undefined && errors[index].item !== undefined">{{
                                                            errors[index].item
                                                        }}</span>

                                                </td>
                                                <td>
                                                    <input readonly type="text"  class="form-control"
                                                           v-model="field.itemCode" placeholder="itemCode" min="0">

                                                </td>
                                                <td>
                                                    <multiselect v-model="field.location" :options="locations"
                                                                 :multiple="false"
                                                                 :close-on-select="true"
                                                                 :clear-on-select="false" :preserve-search="true"
                                                                 placeholder="Select Category"
                                                                 label="LocationName" track-by="LocationCode">

                                                    </multiselect>
                                                    <span class="error"
                                                          v-if="errors[index] !== undefined && errors[index].location !== undefined">{{
                                                            errors[index].location
                                                        }}</span>
                                                </td>
                                                <td>
                                                    <input type="text"  class="form-control"
                                                           v-model="field.quantity" placeholder="quantity" min="1">
                                                    <span class="error"
                                                          v-if="errors[index] !== undefined && errors[index].quantity !== undefined">{{
                                                            errors[index].quantity
                                                        }}</span>

                                                </td>
                                                <td>
                                                    <input type="text" class="form-control"
                                                           v-model="field.itemValue" placeholder="Value" min="1">
                                                    <span class="error"
                                                          v-if="errors[index] !== undefined && errors[index].itemValue !== undefined">{{
                                                            errors[index].itemValue
                                                        }}</span>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-danger btn-sm"
                                                            @click="removeRow(index)"><i
                                                        class="ti-close"></i></button>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>

                                        <div class="col-md-12" style="text-align: end;margin-top:10px">
                                            <submit-form v-if="buttonShow" :name="buttonText"/>
                                        </div>

                                    </div>

                                </div>
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
            buttonText: '',
            status: '',
            confirm: '',
            type: 'add',
            actionType: '',
            buttonShow: false,
            returnVal:'',
            category: [],
            categoryType: '',
            updateCategoryCode:'',
            production_code:'',
            items: [],
            locations: [],
            production_date: '',
            reference: '',
            dayStr: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
            fields: [
                {
                    item: '',
                    itemCode: '',
                    location: {
                        'Active': 'Y',
                        'LocationCode': 'L0001',
                        'LocationName': 'Default'
                    },
                    quantity: '',
                    itemValue: '',
                }
            ],
            errors: [],

        }
    },
    computed: {},
    mounted() {
        this.production_date = moment().format('yyyy-MM-DD');
        $('#add-edit-dept').on('hidden.bs.modal', () => {
            this.$emit('changeStatus')
        });
        bus.$on('add-edit-production', (row) => {
            if (row) {
                let instance = this;
                this.axiosGet('production/get-production-info/' + row.ProductionCode, function (response) {
                    instance.title = 'Update production';
                    instance.buttonText = "Update";
                    instance.buttonShow = true;
                    instance.actionType = 'edit';
                    instance.fields.splice(0, 1)
                    instance.getData();
                    var productionInfo = response.ProductionInfo;

                    //Master
                    instance.production_code = response.ProductionInfo[0].ProductionCode
                    instance.production_date = response.ProductionInfo[0].ProductionDate
                    instance.updateCategoryCode = productionInfo[0].CategoryCode
                    instance.reference = response.ProductionInfo[0].Reference

                    instance.categoryType=[{
                        'Active': response.ProductionInfo[0].Reference,
                        'CategoryCode': response.ProductionInfo[0].CategoryCode,
                        'CategoryName': response.ProductionInfo[0].CategoryName
                    }
                    ]

                    //Details
                    productionInfo.forEach(function (item,index) {
                        instance.fields.push({
                            item: {
                                'ItemName': item.ItemName,
                                'ItemCode': item.ItemCode,
                            },
                            itemCode: item.ItemCode,
                            location: {
                                'Active': 'Y',
                                'LocationCode': item.LocationCode,
                                'LocationName': item.LocationName
                            },
                            quantity: item.Quantity,
                            itemValue: item.Value,
                        })
                    });
                    instance.getItemByCategory();
                }, function (error) {

                });
            } else {
                this.title = 'Add Production';
                this.buttonText = "Add";
                this.UserId = '';

                this.status = '';
                this.buttonShow = true;
                this.actionType = 'add'
                this.getData();
            }
            $("#add-edit-dept").modal("toggle");
            // $(".error-message").html("");
        })
    },
    destroyed() {
        bus.$off('add-edit-production')
    },
    methods: {
        closeModal() {
            $("#add-edit-dept").modal("toggle");
        },
        addRow() {
            this.fields.push({
                item: '',
                itemCode: '',
                location: {
                    'Active': 'Y',
                    'LocationCode': 'L0001',
                    'LocationName': 'Default'
                },
                quantity: '',
                itemValue: '',
            });
        },
        removeRow(id) {
            this.fields.splice(id, 1)
            if (this.errors[id] !== undefined) {
                this.errors.splice(id, 1)
            }
        },
        getData() {
            let instance = this;
            this.axiosGet('production/supporting-data', function (response) {
                instance.category = response.category;
            }, function (error) {
            });
        },
        getItemByCategory() {
            let instance = this;
            let categoryCode = '';
            if(instance.actionType==='add'){
                categoryCode = instance.categoryType.CategoryCode;
            }
            else{
                categoryCode = instance.updateCategoryCode;
            }
            let url = 'production/category-wise-item';
            this.axiosPost(url, {
                CategoryCode:categoryCode ,
            }, (response) => {
                instance.items = response.items;
                instance.locations = response.locations;
            }, (error) => {
                this.errorNoti(error);

            })
        },
        setItemCode(index) {
            let instance = this;
            console.log(instance.fields[index])
            instance.fields[index].itemCode = instance.fields[index].item.ItemCode;
        },
        checkFieldValue() {
            this.errors = [];
            let instance = this;
            this.fields.forEach(function (item, index) {
                if (item.item === '' || item.itemCode === '' || item.location === ''
                    || item.quantity === '' || item.quantity === undefined || item.itemValue === '') {
                    instance.errors[index] = {
                        item: item.item === '' ? 'Item is required' : '',
                        itemCode: item.itemCode === '' ? 'item Code is required' : '',
                        location: item.location === '' ? 'location Code is required' : '',
                        quantity: item.quantity === '' ? 'quantity Code is required' : '',
                        itemValue: item.itemValue === '' ? 'item Value Code is required' : '',

                    };
                }
            });
        },
        onSubmit() {
            this.checkFieldValue();
            console.log(retu)
            if (this.errors.length === 0) {
                this.$store.commit('submitButtonLoadingStatus', true);
                let url = '';
                console.log(this.returnVal)
                if (this.actionType === 'add') {
                    url = 'production/add';
                }
                if (this.returnVal === 'Y') {
                    url = 'production/return';
                }
                else {
                    url = 'production/update';
                }
                this.axiosPost(url, {
                    production_code: this.production_code,
                    production_date: this.production_date,
                    reference: this.reference,
                    categoryType: this.categoryType,
                    details: this.fields,
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
}
</script>

<style src="vue-multiselect/dist/vue-multiselect.min.css">
.card-header {
    background: linear-gradient(269deg, rgb(0 0 0), #007bffb8) !important;
}
</style>
<style>
.datepicker .vue-input, .date-range-picker .vue-input, .timepicker .vue-input, .datetime-picker .vue-input {
    padding: 7px !important;
}
</style>
