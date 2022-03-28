# Types

## Description
Types are what kind of user the account registered is, such as
- regular user  (0)
- trail mod     (1)
- mod           (2)
- higher mod    (3) 
- admin         (4) 
- developer     (5) 

with the higher the number, the better. 

## Why use them
Types are used for 2 reaons, for permisssions and verification
of a certain role. &nbsp;Permissions, which is the 
primary reason for types, is used to determine which user can
use administrative actions on another user, or the verify
that a user is a mod or not, either by the user or
by the software. 

## Types and administrative actions
&nbsp;A user cannot 
use any administrative actions on another user which as the same
or higher type level than them. &nbsp;Take for example, a user
with permission level 1. &nbsp;This user is a moderator, who can
delete, remove, and ban regular users, but not mods.  

This is a problem, as if a moderator does something bad, 
mods cannot do administrative actions on the other mods,
without getting either a admin or a developer involved,
so a **higher mod** was created. &nbsp;Higher mods are
meant to be a bridge between admins and mods, and are 
basically those who are trusted enough to administrate 
the site, but lack the ability to do so. 

## Verification of users
Verification is also a way of making sure a user is who they
claim to be, for both the average user, or for the software. 
&nbsp; Every post or comment, a small type value will be stored
nex to the users name. &nbsp;This type value represents the users
type ***at the time of the post or comment***. This is so that a 
user who has been demoted can still verify that at the time of
the post, they we're still a moderator / admin / developer.

However, the software is different in the sense that it does not
care about the past status, and only checks the current status. &nbsp;The software, because it does not care of any past status
the user once held, will only store the current status in the 
database. If you want to have some way to archive a users
demotions or promotions of their type, you must do it someway else. 