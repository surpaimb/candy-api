<?php
/**
 * Created for someline-starter.
 * User: Libern
 */

namespace GetCandy\Api\Model\Interfaces;


use Illuminate\Database\Eloquent\Model;
use GetCandy\Api\Observers\BaseModelObserver;

interface BaseModelEventsInterface
{

    function onCreating();
    function onCreated();

    function onUpdating();
    function onUpdated();

    function onSaving();
    function onSaved();

    function onDeleting();
    function onDeleted();

    function onRestoring();
    function onRestored();

}