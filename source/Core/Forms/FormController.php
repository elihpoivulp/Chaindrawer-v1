<?php


namespace CD\Core\Forms;

use CD\Core\Controller;

abstract class FormController extends Controller
{
    abstract protected function form(): ModelForm;
}