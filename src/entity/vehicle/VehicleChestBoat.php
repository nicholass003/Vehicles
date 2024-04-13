<?php

/*
 * Copyright (c) 2024 - present nicholass003
 *        _      _           _                ___   ___ ____
 *       (_)    | |         | |              / _ \ / _ \___ \
 *  _ __  _  ___| |__   ___ | | __ _ ___ ___| | | | | | |__) |
 * | '_ \| |/ __| '_ \ / _ \| |/ _` / __/ __| | | | | | |__ <
 * | | | | | (__| | | | (_) | | (_| \__ \__ \ |_| | |_| |__) |
 * |_| |_|_|\___|_| |_|\___/|_|\__,_|___/___/\___/ \___/____/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author  nicholass003
 * @link    https://github.com/nicholass003/
 *
 *
 */

declare(strict_types=1);

namespace nicholass003\vehicles\entity\vehicle;

use nicholass003\vehicles\entity\Vehicle;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

class VehicleChestBoat extends Vehicle{

	public static function getNetworkTypeId() : string{ return EntityIds::CHEST_BOAT; }

	protected function getInitialSizeInfo() : EntitySizeInfo{ return new EntitySizeInfo(0.455, 1.4); }

	public const TAG_WOOD_ID = "WoodId";

	public const ACTION_ROW_RIGHT = 128;
	public const ACTION_ROW_LEFT = 129;

	private int $woodId;

	protected function getInitialDragMultiplier() : float{ return 1.0; }

	protected function getInitialGravity() : float{ return 0.05; }

	public function getName() : string{
		return match($this->getWoodId()){
			0 => "Oak Chest Boat",
			1 => "Spruce Chest Boat",
			2 => "Birch Chest Boat",
			3 => "Jungle Chest Boat",
			4 => "Acacia Chest Boat",
			5 => "Dark Oak Chest Boat",
			6 => "Mangrove Chest Boat",
			default => "Oak Chest Boat"
		};
	}

	public function getRidingPositions() : array{
		return [new Vector3(0, 1, 0)];
	}

	public function getWoodId() : int{
		return $this->woodId;
	}

	public function setWoodId(int $id) : void{
		$this->woodId = $id;
	}

	// TODO: implement more methods
}
