# Boards

Boards a essential part to any imageboard or forum,
and Sarah provides a easy way to remove and add boards.

Board data is stored in the config file 
(configs/config.php), and therefore, boards can
be easily created and removed by adding and
removing board keys and the keys values. &nbsp;
The name of any board must be the key, and the
flavor text that the user see's below it must be
the value to the said key.

## Board Permissions

Board permissions are ways to hide certain boards,
for example, the admin board. &nbsp;The admin board
is only avaliable for users with a [type](types.md) of
2 or above (moderators or higher). &nbsp;Board permissions
can be modified by opening the configs/permission.php file
and adding a array with the boards name, the minimum
required type for the board.