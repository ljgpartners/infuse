<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;


/////////////////////////////// 
// Infuse migrations
///////////////////////////////
class CreateInfuseAdminUsers extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{

		Schema::table('users', function($table)
		{
			$table->string('full_name', 255)->after('username');;
		});

		$user = new InfuseUser;
		$user->username = 'admin';
		$user->full_name = 'Default User';
		$user->email = 'test@temp.com';
		$user->password = 'password'; // This is automatically salted and encrypted
		$user->verified = 1;
		$user->disabled = 0;
		$user->save();

		// Create super user
		$permission = new InfusePermission;
		$permission->name = 'super_admin';
		$permission->description = 'Super Admin';
		$permission->save();

		$role = new InfuseRole;
		$role->name = 'Super Admin';
		$role->description = 'Super Admin';
		$role->level = 0;
		$role->save();

		// Assign the Permission to Default User 
		$role->permissions()->sync(array($permission->id));

		// Assign the Role to the Default User 
		$user->roles()->sync(array($role->id));

		// Create permissions for infuse user
		$permission = new InfusePermission;
		$permission->name = 'infuse_user_create';
		$permission->Description = 'Create admin user';
		$permission->save();

		$permission = new InfusePermission;
		$permission->name = 'infuse_user_update';
		$permission->Description = 'Update admin user';
		$permission->save();

		$permission = new InfusePermission;
		$permission->name = 'infuse_user_view';
		$permission->Description = 'View admin user';
		$permission->save();

		$permission = new InfusePermission;
		$permission->name = 'infuse_user_delete';
		$permission->Description = 'Delete admin user';
		$permission->save();


		Schema::create('password_reminders', function(Blueprint $table)
		{
			$table->string('email')->index();
			$table->string('token')->index();
			$table->timestamp('created_at');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('password_reminders');
	}

}