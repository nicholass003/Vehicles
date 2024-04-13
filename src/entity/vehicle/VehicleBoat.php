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
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\AnimatePacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\player\Player;

class VehicleBoat extends Vehicle{

	public static function getNetworkTypeId() : string{ return EntityIds::BOAT; }

	protected function getInitialSizeInfo() : EntitySizeInfo{ return new EntitySizeInfo(0.455, 1.4); }

	public const TAG_WOOD_ID = "WoodId";

	public const ACTION_ROW_RIGHT = 128;
	public const ACTION_ROW_LEFT = 129;

	private int $woodId;

	protected function getInitialDragMultiplier() : float{ return 1.0; }

	protected function getInitialGravity() : float{ return 0.05; }

	protected function initEntity(CompoundTag $nbt) : void{
		$woodId = $nbt->getInt(self::TAG_WOOD_ID, $default = 0);
		if($woodId > 6 || $woodId < 0){
			$woodId = $default;
		}

		$properties = $this->getNetworkProperties();
		$properties->setGenericFlag(EntityMetadataFlags::AFFECTED_BY_GRAVITY, true);
		$properties->setInt(EntityMetadataProperties::VARIANT, $woodId);

		$this->setWoodId($woodId);
	}

	public function getName() : string{
		return match($this->getWoodId()){
			0 => "Oak Boat",
			1 => "Spruce Boat",
			2 => "Birch Boat",
			3 => "Jungle Boat",
			4 => "Acacia Boat",
			5 => "Dark Oak Boat",
			6 => "Mangrove Boat",
			default => "Oak Boat"
		};
	}

	public function getRidingPositions() : array{
		return [new Vector3(0, 1, 0), new Vector3(-0.5, 1, 0)];
	}

	public function saveNBT() : CompoundTag{
		$nbt = parent::saveNBT();
		$nbt->setInt(self::TAG_WOOD_ID, $this->woodId);
		return $nbt;
	}

	public function getWoodId() : int{
		return $this->woodId;
	}

	public function setWoodId(int $id) : void{
		$this->woodId = $id;
	}

	protected function syncNetworkData(EntityMetadataCollection $properties) : void{
		parent::syncNetworkData($properties);
		$properties->setByte(EntityMetadataProperties::IS_BUOYANT, 1);
		$properties->setGenericFlag(EntityMetadataFlags::STACKABLE, true);
		$properties->setString(EntityMetadataProperties::BUOYANCY_DATA, "{\"apply_gravity\":true,\"base_buoyancy\":1.0,\"big_wave_probability\":0.02999999932944775,\"big_wave_speed\":10.0,\"drag_down_on_buoyancy_removed\":0.0,\"liquid_blocks\":[\"minecraft:water\",\"minecraft:flowing_water\"],\"simulate_waves\":true}");
	}

	public function onUpdate(int $currentTick) : bool{
		$hasUpdate = parent::onUpdate($currentTick);
		if($this->isAlive()){
			$hasUpdate = true;
			// TODO: more logic here
		}
		return $hasUpdate;
	}

	public function attack(EntityDamageEvent $source) : void{
		parent::attack($source);
		if($this->isAlive()){
			$this->doHurtAnimation();
		}
	}

	public function kill() : void{
		parent::kill();
		if($this->lastDamageCause instanceof EntityDamageByEntityEvent){
			$damager = $this->lastDamageCause->getDamager();
			if($damager instanceof Player && !$damager->hasFiniteResources()){
				return;
			}
		}
		foreach($this->getDrops() as $drop){
			$this->getWorld()->dropItem($this->getPosition(), $drop);
		}
	}

	public function getDrops() : array{
		return [
			match($this->getWoodId()){
				0 => VanillaItems::OAK_BOAT()->setCount(1),
				1 => VanillaItems::SPRUCE_BOAT()->setCount(1),
				2 => VanillaItems::BIRCH_BOAT()->setCount(1),
				3 => VanillaItems::JUNGLE_BOAT()->setCount(1),
				4 => VanillaItems::ACACIA_BOAT()->setCount(1),
				5 => VanillaItems::DARK_OAK_BOAT()->setCount(1),
				6 => VanillaItems::MANGROVE_BOAT()->setCount(1),
				default => VanillaItems::OAK_BOAT()->setCount(1)
			}
		];
	}

	public function setPassenger(Entity $entity, int $index) : bool{
		if(parent::setPassenger($entity, $index)){
			$properties = $entity->getNetworkProperties();
			$properties->setByte(EntityMetadataProperties::RIDER_ROTATION_LOCKED, 1);
			$properties->setFloat(EntityMetadataProperties::RIDER_MAX_ROTATION, 90);
			$properties->setFloat(EntityMetadataProperties::RIDER_MIN_ROTATION, 1);
			$properties->setFloat(EntityMetadataProperties::RIDER_SEAT_ROTATION_OFFSET, -90);
			return true;
		}
		return false;
	}

	public function handleAnimatePacket(AnimatePacket $packet) : bool{
		if($this->getRider() === null) return false;

		switch($packet->action){
			case self::ACTION_ROW_RIGHT:
				$this->getNetworkProperties()->setFloat(EntityMetadataProperties::PADDLE_TIME_RIGHT, $packet->float);
				$this->networkPropertiesDirty = true;
				return true;
			case self::ACTION_ROW_LEFT:
				$this->getNetworkProperties()->setFloat(EntityMetadataProperties::PADDLE_TIME_LEFT, $packet->float);
				$this->networkPropertiesDirty = true;
				return true;
		}
		return false;
	}
}
