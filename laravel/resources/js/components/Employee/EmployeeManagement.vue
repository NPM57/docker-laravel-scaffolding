<template>
    <a-table
        :columns="columns"
        :row-key="record => record.id"
        :data-source="data"
        :loading="isLoading"
        :pagination="pagination"
        @change="(e) => handleTableChange(e)"
    >
        <template #title>
            <div style="text-align: right">
                <a-button @click="handleCreate">Create new employee</a-button>
            </div>
        </template>
        <template #bodyCell="{ column, record }">
            <template v-if="column.key === 'company'">
                <div style="flex-direction: row; display: flex; align-items: center;">
                    <div style="margin-right: 2rem">
                        <a-avatar :src="record.company.logo_url"/>
                    </div>
                    <div style="flex-direction: column; display: flex">
                        <div>
                            <a :href="record.company.website"> {{ record.company.name }} </a>
                        </div>
                        <div>
                            {{ record.company.email }}
                        </div>
                    </div>
                </div>
            </template>
            <template v-if="column.key === 'action'">
                <a-button @click="handleEdit(record.id)">Edit</a-button>
                <a-divider type="vertical"/>
                <a-button @click="handleDelete(record.id)">Delete</a-button>
            </template>
        </template>
    </a-table>

    <a-modal
        v-if="showEditModal"
        v-model:visible="showEditModal"
        title="Editing"
        :confirm-loading="isEditing"
        ok-text="Update"
        @close="handleCancelForEditModal"
        @cancel="handleCancelForEditModal"
        @ok="handleOkForEditModal"
    >
        <a-form layout="horizontal" :label-col="{ style: { width: '150px' } }" :wrapper-col=" { span: 14 }">
            <a-form-item label="First Name" required>
                <a-input v-model:value="selectedEmployee.first_name" placeholder="First name is required"/>
            </a-form-item>
            <a-form-item label="Last Name" required>
                <a-input v-model:value="selectedEmployee.last_name" placeholder="Last name is required"/>
            </a-form-item>
            <a-form-item label="Email">
                <a-input v-model:value="selectedEmployee.email" placeholder="Email address"/>
            </a-form-item>
            <a-form-item label="Phone">
                <a-input v-model:value="selectedEmployee.phone" placeholder="Phone number"/>
            </a-form-item>
            <a-form-item label="Company">
                <a-input v-model:value="selectedEmployee.company.name" placeholder="Company name" disabled/>
            </a-form-item>
            <a-form-item label="Assign new company">
                <a-select
                    v-model:value="searchValueEdit"
                    show-search
                    placeholder="Search company name"
                    style="width: 200px"
                    :default-active-first-option="false"
                    :show-arrow="true"
                    :filter-option="false"
                    :not-found-content="null"
                    :allowClear="true"
                    :loading="isLoadingSearchEdit"
                    :options="searchDataEdit"
                    @search="handleSearchEdit"
                ></a-select>
            </a-form-item>
        </a-form>
    </a-modal>

    <a-modal
        v-if="showCreateModal"
        v-model:visible="showCreateModal"
        title="Create new employee"
        :confirm-loading="isCreating"
        ok-text="Create"
        @close="handleCancelForCreateModal"
        @cancel="handleCancelForCreateModal"
        @ok="handleOkForCreateModal"
    >
        <a-form layout="horizontal" :label-col="{ style: { width: '150px' } }" :wrapper-col=" { span: 14 }">
            <a-form-item label="First Name" required>
                <a-input v-model:value="newEmployee.first_name" placeholder="First name is required"/>
            </a-form-item>
            <a-form-item label="Last Name" required>
                <a-input v-model:value="newEmployee.last_name" placeholder="Last name is required"/>
            </a-form-item>
            <a-form-item label="Email">
                <a-input v-model:value="newEmployee.email" placeholder="Email address"/>
            </a-form-item>
            <a-form-item label="Phone">
                <a-input v-model:value="newEmployee.phone" placeholder="Phone number"/>
            </a-form-item>
            <a-form-item label="Assign new company">
                <a-select
                    v-model:value="searchValueCreate"
                    show-search
                    placeholder="Search company name"
                    style="width: 200px"
                    :default-active-first-option="false"
                    :show-arrow="true"
                    :filter-option="false"
                    :not-found-content="null"
                    :allowClear="true"
                    :options="searchDataCreate"
                    :loading="isLoadingSearchCreate"
                    @search="handleSearchCreate"
                ></a-select>
            </a-form-item>
        </a-form>
    </a-modal>

    <a-modal
        v-if="showDeleteModal"
        v-model:visible="showDeleteModal"
        title="Delete confirmation"
        :confirm-loading="isDeleting"
        ok-text="Delete"
        @ok="handleOkForDeleteModal"
    >
        <a-form layout="horizontal" :label-col="{ style: { width: '150px' } }" :wrapper-col=" { span: 14 }">
            <a-form-item label="First Name" required>
                <a-input v-model:value="deleteEmployee.first_name" placeholder="First name is required" disabled/>
            </a-form-item>
            <a-form-item label="Last Name" required>
                <a-input v-model:value="deleteEmployee.last_name" placeholder="Last name is required" disabled/>
            </a-form-item>
            <a-form-item label="Email">
                <a-input v-model:value="deleteEmployee.email" placeholder="Email address" disabled/>
            </a-form-item>
            <a-form-item label="Phone">
                <a-input v-model:value="deleteEmployee.phone" placeholder="Phone number" disabled/>
            </a-form-item>
            <a-form-item label="Company">
                <a-input v-model:value="deleteEmployee.company.name" placeholder="Company name" disabled/>
            </a-form-item>
        </a-form>
        <p style="font-weight: bold; color: red">Are you sure you want to delete this Employee?</p>
    </a-modal>
</template>
<script>
import axios from 'axios';
import _ from 'lodash';
import {message, Upload, notification} from "ant-design-vue";

export default {
    components: {},
    data() {
        return {
            isLoading: false,
            columns: [
                {
                    title: 'First Name',
                    dataIndex: 'first_name',
                    key: 'first_name',
                    sorter: true,
                },
                {
                    title: 'Last Name',
                    dataIndex: 'last_name',
                    key: 'last_name',
                    sorter: true,
                },
                {
                    title: 'Company',
                    dataIndex: 'company',
                    key: 'company',
                    sorter: true,
                    width: '20%',
                },
                {
                    title: 'Email',
                    dataIndex: 'email',
                    key: 'email',
                    sorter: true,
                    width: '20%',
                },
                {
                    title: 'Phone',
                    dataIndex: 'phone',
                    key: 'phone',
                    width: '20%',
                },
                {
                    title: 'Action',
                    key: 'action',
                    width: '10%',
                }
            ],
            data: null,
            total: 0,
            currentPage: 1,
            limit: 10,
            selectedEmployee: {},
            newEmployee: {},
            deleteEmployee: {},
            showEditModal: false,
            showCreateModal: false,
            showDeleteModal: false,
            isEditing: false,
            isCreating: false,
            isDeleting: false,
            isLoadingSearchEdit: false,
            isLoadingSearchCreate: false,
            searchDataEdit: [],
            searchDataCreate: [],
            searchValueEdit: null,
            searchValueCreate: null,
        }
    },
    created() {
        this.getEmployees(this.limit);
    },

    computed: {
        pagination() {
            return {
                total: this.total,
                current: this.currentPage,
                pageSize: this.limit,
            }
        },
    },

    methods: {
        getEmployees(limit = 10, page = 1) {
            this.isLoading = true;
            const params = {
                limit,
                page,
            }
            return axios.get('/api/employee', {params})
                .then((response) => {
                    this.isLoading = false;
                    this.data = response.data.data;
                    this.total = response.data.total;
                    this.currentPage = response.data.current_page;
                })
                .catch((error) => {
                    this.isLoading = false;
                    message.error(error.response.data.message, 3);
                });
        },

        handleTableChange(e) {
            this.limit = e.pageSize;
            this.getEmployees(this.limit, e.current);
        },

        /**
         *
         *  Method for Delete modal
         *
         */
        handleDelete(id) {
            this.showDeleteModal = true;
            this.deleteEmployee = JSON.parse(JSON.stringify(this.data.find((el) => el.id === id)));
        },

        handleOkForDeleteModal() {
            const params = {
                id: this.deleteEmployee.id,
            }

            axios.delete('/api/employee', {params})
                .then((response) => {
                    this.getEmployees(this.limit);
                    this.deleteEmployee = {};
                    message.success(response.data.message, 3);
                    this.isDeleting = false
                    this.showDeleteModal = false;
                })
                .catch((error) => {
                    message.error(error.response.data.message, 3);
                });
        },

        /**
         *
         *  Method for Edit modal
         *
         */
        handleEdit(id) {
            this.showEditModal = true;
            this.selectedEmployee = JSON.parse(JSON.stringify(this.data.find((el) => el.id === id)));
        },

        handleCancelForEditModal(e) {
            this.showEditModal = false;
        },

        handleOkForEditModal(e) {
            e.preventDefault();

            const data = new FormData();
            if (this.selectedEmployee.first_name) {
                data.set('first_name', this.selectedEmployee.first_name);
            } else {
                return notification.error({
                    message: 'Form validation failed',
                    description: 'Employee first name is required.',
                })
            }

            if (this.selectedEmployee.last_name) {
                data.set('last_name', this.selectedEmployee.last_name);
            } else {
                return notification.error({
                    message: 'Form validation failed',
                    description: 'Employee last name is required.',
                })
            }

            if (this.selectedEmployee.email) {
                data.set('email', this.selectedEmployee.email);
            }

            if (this.selectedEmployee.phone) {
                data.set('phone', this.selectedEmployee.phone);
            }

            // New company id
            if (this.searchValueEdit) {
                data.set('company_id', this.searchValueEdit);
            } else {
                data.set('company_id', this.selectedEmployee.company_id);
            }

            if (this.selectedEmployee.id) {
                data.set('id', this.selectedEmployee.id);

                // PHP-Symfony does not send the FormData along with patch request
                axios.post('/api/employee/edit', data)
                    .then((response) => {
                        this.getEmployees(this.limit);
                        message.success(response.data.message, 3);
                        this.isEditing = false;
                        this.showEditModal = false;
                        this.searchValueEdit = null;
                    })
                    .catch((error) => {
                        message.error(error.response.data.message, 3);
                    });
            } else {
                message.error('The selected employee does not have associated id - reloading the list of appropriate employees', 3);
                this.getEmployees(this.limit);
            }

        },

        /**
         *
         *  Method for Create modal
         *
         */
        handleCreate() {
            this.showCreateModal = true;
        },

        handleCancelForCreateModal(e) {
            this.showCreateModal = false;
            this.newEmployee = {};
        },

        handleOkForCreateModal(e) {
            e.preventDefault();
            // upload file and call a request to update the selected employee
            const data = new FormData();
            if (this.newEmployee.first_name) {
                data.set('first_name', this.newEmployee.first_name);
            } else {
                return notification.error({
                    message: 'Form validation failed',
                    description: 'Employee first name is required.',
                })
            }

            if (this.newEmployee.last_name) {
                data.set('last_name', this.newEmployee.last_name);
            } else {
                return notification.error({
                    message: 'Form validation failed',
                    description: 'Employee last name is required.',
                })
            }

            if (this.newEmployee.email) {
                data.set('email', this.newEmployee.email);
            }

            if (this.newEmployee.phone) {
                data.set('phone', this.newEmployee.phone);
            }

            // New company id
            if (this.searchValueCreate) {
                data.set('company_id', this.searchValueCreate);
            } else {
                return notification.error({
                    message: 'Form validation failed',
                    description: 'Employee must be assigned to a company.',
                })
            }

            axios.post('/api/employee/create', data)
                .then((response) => {
                    this.getEmployees(this.limit);
                    message.success(response.data.message, 3);
                    this.isCreating = false;
                    this.showCreateModal = false;
                    this.searchValueCreate = null;
                })
                .catch((error) => {
                    message.error(error.response.data.message, 3);
                });
        },

        /**
         *
         *  Method for company search
         *
         */
        handleSearchEdit: _.debounce(function (value, limit = 10, page = 1) {
            this.isLoadingSearchEdit = true;
            const params = {
                limit,
                page,
            }
            return axios.get(`/api/company?search=${value}`, {params})
                .then((response) => {
                    this.searchDataEdit = response.data.data.map((el) => {
                        return ({
                            label: el.name,
                            value: el.id,
                        });
                    });
                })
                .catch((error) => {
                    message.error(error.response.data.message, 3);
                })
                .finally(() => {
                    this.isLoadingSearchEdit = false;
                });
        }, 500),

        handleSearchCreate: _.debounce(function (value, limit = 10, page = 1) {
            this.isLoadingSearchCreate = true;
            const params = {
                limit,
                page,
            }
            return axios.get(`/api/company?search=${value}`, {params})
                .then((response) => {
                    this.searchDataCreate = response.data.data.map((el) => {
                        return ({
                            label: el.name,
                            value: el.id,
                        });
                    });
                })
                .catch((error) => {
                    message.error(error.response.data.message, 3);
                })
                .finally(() => {
                    this.isLoadingSearchCreate = false;
                });
        }, 500),
    },
}
</script>

