<?php
echo shell_exec("git tag -d $(git tag -l)");
echo shell_exec("git pull");
