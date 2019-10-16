<template>
    <div class="container">
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Users Table</h3>

              <div class="card-tools">
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#userModal">
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

                  <tr v-for="user in users" :key="user.id">
                    <td>{{user.id}}</td>
                    <td>{{user.name}}</td>
                    <td>{{user.email}}</td>
                    <td>{{user.type | uptext}}</td>
                    <td>{{user.created_at | formatDate}}</td>
                    <td>
                      <a href="#"><i class="fa fa-edit text-blue"></i> Edit</a>
                      &nbsp;
                      <a @click="deleteUser(user.id)" href="#"><i class="fa fa-trash text-red"></i> Delete</a>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <!-- /.card-body -->
          </div>
          <!-- /.card -->
        </div>
      </div>
      <!-- Modal -->
      <div class="modal fade" id="userModal" tabindex="-1" role="dialog" aria-labelledby="userModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="userModalLabel"><i class="fas fa-user-plus"></i> Add New User</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <form @submit.prevent="createUser" id="createUser">
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
                <button type="submit" class="btn btn-primary">Create</button>
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
            users: {},
            form: new Form({
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
              axios.get('api/user').then(({data}) => this.users = data.data);
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
                    Swal('Failed', 'There was an error.', 'warning');
                  );
                }
            });
          }
        },
        created() {
            this.loadUsers();
            Fire.$on('AfterCreate', () => {
              this.loadUsers();
            });
            Fire.$on('resetForm', () => {
              $('#createUser').trigger('reset');
            });
        },
        mounted() {
            console.log('Component mounted.')
        }
    }
</script>
