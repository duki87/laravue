<template>
    <div class="container">
      <div class="row" v-if="$Gate.isAdminOrAuthor()">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Users Table</h3>

              <div class="card-tools">
                <button @click="addModal" type="button" class="btn btn-primary">
                  <i class="fas fa-user-plus"></i>
                  Add User
                </button>
              </div>
            </div>
            <!-- /.card-header -->
            <div class="card-body table-responsive p-0">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Type</th>
                    <th>Date Joined</th>
                    <th>Modify</th>
                  </tr>
                </thead>
                <tbody>

                  <tr v-for="user in users.data" :key="user.id">
                    <td>{{user.id}}</td>
                    <td>{{user.name}}</td>
                    <td>{{user.email}}</td>
                    <td>{{user.type | uptext}}</td>
                    <td>{{user.created_at | formatDate}}</td>
                    <td>
                      <a @click="editModal(user)" href="#"><i class="fa fa-edit text-blue"></i> Edit</a>
                      &nbsp;
                      <a @click="deleteUser(user.id)" href="#"><i class="fa fa-trash text-red"></i> Delete</a>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <!-- /.card-body -->
            <div class="card-footer">
              <pagination :data="users" @pagination-change-page="getResults"></pagination>
            </div>
          </div>
          <!-- /.card -->
        </div>

      </div>

      <div v-if="!$Gate.isAdminOrAuthor()">
        <not-found></not-found>
      </div>
      <!-- Modal -->
      <div class="modal fade" id="userModal" tabindex="-1" role="dialog" aria-labelledby="userModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="userModalLabel">
              <span v-show="!editUserMode">
                <i class="fas fa-user-plus"></i>
                Add New User
              </span>
              <span v-show="editUserMode">
                <i class="fas fa-user-edit"></i>
                Edit User
              </span>
              </h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <form @submit.prevent="editUserMode ? updateUser() : createUser()" id="createUser">
              <div class="modal-body">
                <div class="form-group">
                  <input v-model="form.name" type="text" name="name" id="name"
                    class="form-control" :class="{ 'is-invalid': form.errors.has('name') }" placeholder="Name">
                  <has-error :form="form" field="name"></has-error>
                </div>

                <div class="form-group">
                  <input v-model="form.email" type="text" name="email" id="email"
                    class="form-control" :class="{ 'is-invalid': form.errors.has('email') }" placeholder="Email">
                  <has-error :form="form" field="email"></has-error>
                </div>

                <div class="form-group">
                  <input v-model="form.password" type="password" id="password" name="password"
                    class="form-control" :class="{ 'is-invalid': form.errors.has('password') }" placeholder="Password">
                  <has-error :form="form" field="password"></has-error>
                </div>

                <div class="form-group">
                  <select id="type" v-model="form.type" name="type"
                    class="form-control" :class="{ 'is-invalid': form.errors.has('type') }">
                      <option value="">Select User Role</option>
                      <option value="admin">Admin</option>
                      <option value="user">User</option>
                      <option value="author">Author</option>
                  </select>
                  <has-error :form="form" field="type"></has-error>
                </div>

                <div class="form-group">
                  <textarea v-model="form.bio" type="text" name="bio" id="bio"
                    class="form-control" :class="{ 'is-invalid': form.errors.has('bio') }" placeholder="User Bio">
                  </textarea>
                  <has-error :form="form" field="bio"></has-error>
                </div>

              </div>
              <div class="modal-footer">
                <button v-show="!editUserMode" type="submit" class="btn btn-primary">Create</button>
                <button v-show="editUserMode" type="submit" class="btn btn-success">Update</button>
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
</template>

<script>
    export default {
        data() {
          return {
            editUserMode: false,
            users: {},
            form: new Form({
              id: '',
              name: '',
              email: '',
              password: '',
              type: '',
              bio: '',
              photo: ''
            })
          }
        },
        methods: {
          createUser() {
              this.$Progress.start();
              this.form.post('/api/user')
                .then(() => {
                    Fire.$emit('AfterCreate');
                    Fire.$emit('resetForm');
                    $('#userModal').modal('hide');
                    Toast.fire({
                      type: 'success',
                      title: 'User Created successfully'
                    });
                    this.$Progress.finish();
                  }
                ).catch(e => {console.log(e)});
          },
          loadUsers() {
              if(this.$Gate.isAdminOrAuthor()) {
                axios.get('api/user').then(({data}) => this.users = data);
              }
              //this.$router.go(-1);
          },

          getResults(page = 1) {
            axios.get('api/user?page=' + page)
            .then(response => {
              //let usersObj = Object.assign({}, this.users);
              this.users = response.data;
            });
          },

          deleteUser(userId) {
            Swal.fire({
              title: 'Are you sure to delete this user?',
              text: "You won't be able to revert this!",
              type: 'warning',
              showCancelButton: true,
              confirmButtonColor: '#3085d6',
              cancelButtonColor: '#d33',
              confirmButtonText: 'Yes, delete it!'
              }).then((result) => {
                if(result.value) {
                  this.form.delete('api/user/'+userId)
                    .then(() => {
                      Swal.fire(
                        'Deleted!',
                        'User has been deleted.',
                        'success'
                      );
                      Fire.$emit('AfterCreate');
                  }).catch(
                    () => {
                      Swal.fire({
                        type: 'error',
                        title: 'Error',
                        text: 'There was an error!'
                      })
                    }
                  );
                }
            }).catch(
              console.log('error')
            )
          },
          updateUser() {
            this.$Progress.start();
            this.form.put('api/user/'+this.form.id)
              .then((res) => {
                this.$Progress.finish();
                $('#userModal').modal('hide');
                Swal.fire(
                  'User Updated!',
                  'User data has been updated.',
                  'success'
                );
                Fire.$emit('AfterCreate');
              })
              .catch(() => {this.$Progress.fail();});
          },
          editModal(userData) {
             this.editUserMode = true;
             this.form.reset();
             this.form.fill(userData);
             $('#userModal').modal('show');
          },
          addModal() {
             this.editUserMode = false;
             this.form.reset();
             $('#userModal').modal('show');
          },
        },
        created() {
            this.loadUsers();
            Fire.$on('AfterCreate', () => {
              this.loadUsers();
            });
            Fire.$on('resetForm', () => {
              $('#createUser').trigger('reset');
            });
            Fire.$on('searching', () => {
              let keywords = this.$parent.search;
              //console.log(keywords);
              axios.get('api/findUser?keywords=' + keywords)
              .then((res) => {
                this.users = res.data
                ;
              })
              .catch(() => {
                Swal.fire({
                  type: 'error',
                  title: 'Error',
                  text: 'There was an error!'
                })
              })
            });
        },
        mounted() {
            console.log('Component mounted.')
        }
    }
</script>
