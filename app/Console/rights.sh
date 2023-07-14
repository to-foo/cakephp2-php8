#!/bin/bash
echo -e "-> persistent  \e[1;34m   
  __  __ ____   ____  
 |  \/  |  _ \ / __ \ 
 | \  / | |_) | |  | |
 | |\/| |  _ <| |  | |
 | |  | | |_) | |__| |
 |_|  |_|____/ \___\_\
\e[0m"                   


echo -e '\e[1;34m Unlocking files... \e[0m' 
SCRIPT="$(readlink --canonicalize-existing "$0")"
SCRIPTPATH="$(dirname "$SCRIPT")"
echo 'Current Location: ' $SCRIPTPATH 

chmod 755 $SCRIPTPATH'/cake.php'
if [ $? -eq 0 ]; then
   echo -e "-> cake.php \e[1;32m [OK]  \e[0m"
else
   echo -e "-> cake.php \e[1;31m [FAIL] \e[0m"
fi

chmod 755 $SCRIPTPATH'/cake'
if [ $? -eq 0 ]; then
   echo -e "-> cake \e[1;32m     [OK] \e[0m"
else
   echo -e "-> cake \e[1;31m [FAIL] \e[0m"
fi

chmod 755 $SCRIPTPATH'/cake.bat'
if [ $? -eq 0 ]; then
   echo -e "-> cake.bat \e[1;32m [OK] \e[0m"
else
   echo -e "-> cake.bat \e[1;31m [FAIL] \e[0m"
fi

echo -e 'Done!'
