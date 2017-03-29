<?php

namespace Application;

	return array(
		
		// application-wide labels
		'common.create' => 'Create New',
		'common.view' => 'View',
		'common.edit' => 'Edit',
		'common.delete' => 'Delete',
		'common.save' => 'Save', 
		'common.download' => 'Download', 
		'common.cancel' => 'Cancel', 

		'common.dashboard' => 'Dashboard', 
		'common.reports' => 'Reports', 
		'common.settings' => 'Settings', 
		'common.login' => 'Sign In', 
		'common.logout' => 'Sign Out', 
		'common.home' => 'Home',
		'common.confirm-action' => 'Confirm Action', 
		'common.confirm-proceed' => 'Do you wish to proceed?', 
		'common.confirm-delete' => 'This operation will delete the %s %s and all related data from the system.',
		'common.yes' => 'Yes', 
		'common.no' => 'No',
		'common.close' => 'Close',
		'common.no-undo' => 'This operation cannot be undone.',
		'common.alert-action' => 'Alert', 
		'common.alert-access-denied' => 'You do not have sufficient privileges to perform this action.',
		'common.confirm-permissions-revoke' => 'This operation will revoke %s %s\'s access to the %s %s.',
		'common.confirm-permissions-grant' => 'This operation will grant %s %s access to the %s %s.',

		'activity-stream.created' => '%s created this case', 
		'activity-stream.deleted' => '%s deleted this case', 
		'activity-stream.updated' => '%s modified the %s from %s to %s', 
		'activity-stream.associated' => '%s added the %s %s to this case', 
		'activity-stream.dissociated' => '%s removed the %s %s from this case', 
		'activity-stream.requested' => '%s requested the %s %s', 
		'activity-stream.empty' => 'empty', 
		'activity-stream.opened' => '%s opened this case', 
		'activity-stream.closed' => '%s closed this case', 
		'activity-stream.recent-activity' => 'Recent Activity', 

		// job entity
		'job.entity' => 'Case',
		'job.jobs' => 'Cases',
		'job.open-jobs' => 'Open Cases',
		'job.id' => '#', 
		'job.title' => 'Title',
		'job.creation-time' => 'Created on',
		'job.description' => 'Description', 
		'job.comments' => 'Comments', 
		'job.permissions' => 'Privileges', 
		'job.close' => 'Close', 
		'job.grant' => 'Add Collaborator', 
		'job.revoke' => 'Remove Collaborator', 
		'job.collaborators' => 'Collaborators', 
		'job.permission-manage' => 'Owner with all privileges', 
		'job.permission-edit' => 'Collaborator with edit privileges', 
		'job.permission-view' => 'Collaborator with view privileges', 
		'job.confirm-close' => 'This operation will close the %s %s and hide all related data in the system.',
		'job.confirm-open' => 'This operation will open the %s %s and make all related data visible in the system.',
		'job.alert-owner-permissions-revoke' => 'This %s owner\'s privileges cannot be revoked.',

		// label entity
		'label.entity' => 'Label',
		'label.labels' => 'Labels',
		'label.id' => '#', 
		'label.colour' => 'Colour',
		'label.name' => 'Name', 
		'label.current-labels' => 'Current Labels',

		// file entity
		'file.entity' => 'File',
		'file.current-files' => 'Current Files',
		'file.name' => 'Name',
		'file.creation-time' => 'Date',
		'file.add' => 'Add File',
    
    	// user entity
		'user.entity' => 'User',
		'user.users' => 'Users',
		'user.current-users' => 'Current Users',
		'user.my-profile' => 'Profile',
		'user.id' => '#', 
		'user.name' => 'Name', 
		'user.username' => 'Email address',
		'user.password' => 'Password',
		'user.role' => 'Role',
		'user.self' => 'You',
		'user.role-administrator' => 'Administrator',
		'user.role-employee' => 'Employee',
		'user.role-customer' => 'Customer',
		'user.status' => 'Status',
		'user.activate' => 'Activate', 
		'user.deactivate' => 'Deactivate', 
		'user.confirm-deactivate' => 'This operation will deactivate the %s %s.',
		'user.confirm-activate' => 'This operation will activate the %s %s.',
		'user.alert-min-threshold' => 'This application requires a minimum of 1 active %s.',
		'user.alert-owner-open-jobs' => 'This %s has at least one open %s and cannot be deleted.'
    
	);
?>