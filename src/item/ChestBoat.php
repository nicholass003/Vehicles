<?php

/*
 *       _      _           _                ___   ___ ____
 *      (_)    | |         | |              / _ \ / _ \___ \
 * _ __  _  ___| |__   ___ | | __ _ ___ ___| | | | | | |__) |
 *| '_ \| |/ __| '_ \ / _ \| |/ _` / __/ __| | | | | | |__ <
 *| | | | | (__| | | | (_) | | (_| \__ \__ \ |_| | |_| |__) |
 *|_| |_|_|\___|_| |_|\___/|_|\__,_|___/___/\___/ \___/____/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author nicholass003
 * @link https://github.com/nicholass003/
 *
 */

declare(strict_types=1);

namespace nicholass003\vehicles\item;

use pocketmine\item\BoatType;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;

class ChestBoat extends Item{

    private BoatType $boatType;

    public function __construct(ItemIdentifier $identifier, string $name, BoatType $boatType){
        parent::__construct($identifier, $name);
        $this->boatType = $boatType;
    }

    public function getType() : BoatType{
        return $this->boatType;
    }

    public function getFuelTime() : int{
        return 1600;
    }

    public function getMaxStackSize() : int{
        return 1;
    }
}