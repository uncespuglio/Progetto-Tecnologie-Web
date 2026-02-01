<?php

unset($_SESSION['user_id']);
flash('ok', 'Logout effettuato.');
redirect(url('?p=home'));
