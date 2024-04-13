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

namespace nicholass003\vehicles\item;

use pocketmine\data\bedrock\item\ItemTypeNames;
use pocketmine\item\BoatType;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemTypeIds;
use pocketmine\utils\CloningRegistryTrait;
use function str_replace;
use function strtolower;

final class ExtraVanillaItem{
	use CloningRegistryTrait;

	private function __construct(){}

	protected static function register(string $name, Item $item) : void{
		self::_registryRegister($name, $item);
	}

	public static function getAll() : array{
		return self::_registryGetAll();
	}

	protected static function setup() : void{
		foreach(BoatType::getAll() as $type){
			self::register(strtolower(str_replace(" ", "_", $type->getDisplayName())) . "_chest_boat", new ChestBoat(new ItemIdentifier(ItemTypeIds::newId()), (match($type){
				BoatType::OAK() => ItemTypeNames::OAK_CHEST_BOAT,
				BoatType::SPRUCE() => ItemTypeNames::SPRUCE_CHEST_BOAT,
				BoatType::BIRCH() => ItemTypeNames::BIRCH_CHEST_BOAT,
				BoatType::JUNGLE() => ItemTypeNames::JUNGLE_CHEST_BOAT,
				BoatType::ACACIA() => ItemTypeNames::ACACIA_CHEST_BOAT,
				BoatType::DARK_OAK() => ItemTypeNames::DARK_OAK_CHEST_BOAT,
				BoatType::MANGROVE() => ItemTypeNames::MANGROVE_CHEST_BOAT
			}), $type));
		}
	}
}
