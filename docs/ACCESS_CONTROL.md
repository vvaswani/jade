# Access Control

## Overview

### Roles

 * Every user has a system role: administrator, employee or customer.
 * The user's system role defines access to user accounts.
 	* Administrators can manage all user accounts.
 	* Employees and customers can only manage their own account. 
 * The user's system role also defines access to system resources.
   	* Administrators can manage all system resources. 
 	* Employees and customers can access system resources based on resource-specific permissions. 

### Resources

 * The system is composed of resources: jobs, labels, configuration, ...
 * Some resources have sub-resources: files belong to jobs, ...
 * Each resource defines a set of permissions, which are assigned on a per-resource basis.
 	* Administrators have all permissions to each resource.
 	* Employees and customers who create a resource have all permissions to it.
 	* Other employees and customers have limited or nil permissions to resources.
 * Resource permissions are defined automatically by the system in some cases (labels) and can be manually granted/revoked in others (jobs). 

## Permissions and Permission Matrix

### Permissions

#### Labels

Permissions for label resources are defined and managed by the system.

 * `LABEL.MANAGE`: All access. Automatically granted by the system to administrators and the label creator. 

#### Jobs

Permissions for job resources are initially defined by the system and can be additionally granted/revoked by users.

 * `JOB.MANAGE`: All access. Automatically granted by the system to administrators and the job creator. 
 * `JOB.EDIT`: Limited write access. Manually granted by administrators and the job creator.
 * `JOB.VIEW`: Read access. Manually granted by administrators and the job creator.

### Permission Matrix

|                              | Administrator | Employee | Customer |
|------------------------------|---------------|----------|----------|
| Create user                  |       Y       |    Y     |    N     |
| List all users               |       Y       |    N     |    N     |
| Modify user data excl. role  |       Y       |    Y *1  |    Y *1  |
| Activate/deactivate user     |       Y       |    N     |    N     |
| Delete user                  |       Y       |    N     |    N     |
|                              |               |          |          |
| Create label                 |       Y       |    Y     |    N     |
| List all labels              |       Y       |    Y     |    Y     |
| Modify label data            |       Y       |    Y *2  |    N     |
| Delete label                 |       Y       |    Y *2  |    N     |
|                              |               |          |          |
| Create job                   |       Y       |    Y     |    N     |
| List jobs                    |       Y       |    Y *3  |    Y *3  |
| Modify job data              |       Y       |    Y *4  |    Y *4  |
| Open/close job               |       Y       |    Y *5  |    N     |
| Delete job                   |       Y       |    Y *5  |    N     |
| Grant access to job          |       Y       |    Y *5  |    N     |
| Revoke access to job         |       Y       |    Y *5  |    N     |
| Add file to job              |       Y       |    Y *4  |    Y *4  |
| Remove file from job         |       Y       |    Y *4  |    Y *4  |
| View file associated with job|       Y       |    Y *3  |    Y *3  |
|------------------------------|---------------|----------|----------|


*1 = only for the user's own data
*2 = only for labels created by the user
*3 = only if the user has VIEW or higher permissions for the job
*4 = only if the user has EDIT or higher permissions for the job
*5 = only if the user has MANAGE permissions for the job