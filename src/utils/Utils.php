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

namespace nicholass003\vehicles\utils;

use nicholass003\vehicles\entity\Vehicle;
use nicholass003\vehicles\entity\vehicle\VehicleBoat;
use nicholass003\vehicles\entity\vehicle\VehicleChestBoat;
use pocketmine\block\Block;
use pocketmine\entity\Location;
use pocketmine\item\BoatType;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\World;

class Utils{
	public const VEHICLE_TYPE_BOAT = 1;
	public const VEHICLE_TYPE_CHEST_BOAT = 2;

	public const LIQUID_BLOCK = 1;
	public const SOLID_BLOCK = 1.375;

	public static function createEntityVehicle(World $world, Vector3 $pos, float $yaw, float $pitch, int $vehicleType, int $boatType = 0) : Vehicle{
		return match($vehicleType){
			self::VEHICLE_TYPE_BOAT => new VehicleBoat(Location::fromObject($pos, $world, $yaw, $pitch), CompoundTag::create()->setInt(VehicleBoat::TAG_WOOD_ID, $boatType)),
			self::VEHICLE_TYPE_CHEST_BOAT => new VehicleChestBoat(Location::fromObject($pos, $world, $yaw, $pitch), CompoundTag::create()->setInt(VehicleChestBoat::TAG_WOOD_ID, $boatType))
		};
	}

	/**
	 * Matches a BoatType to it's corresponding numerical value.
	 * This method is intended for use with @VehicleBoat only.
	 */
	public static function matchBoatType(BoatType $boatType) : int{
		return match($boatType){
			BoatType::OAK() => 0,
			BoatType::SPRUCE() => 1,
			BoatType::BIRCH() => 2,
			BoatType::JUNGLE() => 3,
			BoatType::ACACIA() => 4,
			BoatType::DARK_OAK() => 5,
			BoatType::MANGROVE() => 6,
			default => 0
		};
	}

	/**
	 * Spawns a boat with the specified y position.
	 * This method is intended for use with @VehicleBoat only.
	 */
	public static function checkBlockType(Block $block) : float|int{
		return $block->isSolid() ? self::SOLID_BLOCK : self::LIQUID_BLOCK;
	}
}
