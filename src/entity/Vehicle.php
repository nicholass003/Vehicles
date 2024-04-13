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

namespace nicholass003\vehicles\entity;

use pocketmine\entity\Entity;
use pocketmine\entity\Location;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AnimatePacket;
use pocketmine\network\mcpe\protocol\SetActorLinkPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityLink;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use function array_search;
use function array_values;
use function count;

abstract class Vehicle extends Entity{
	/** @var int[] */
	public static array $riders = [];

	/** @var Entity[] */
	private array $passangers = [];

	private bool $rollingDirection = true;

	private int $hurtTime = 0;

	private int $hurtDirection = 0;

	abstract public function getName() : string;

	/** @var Vector3[] */
	abstract public function getRidingPositions() : array;

	public function absoluteMove(Vector3 $pos, float $yaw = 0.0, float $pitch = 0.0) : void{
		$this->location = Location::fromObject($pos, $this->location->world, $yaw, $pitch);
		$this->updateMovement();
	}

	public function getRollingAmplitude() : int{
		return $this->hurtTime;
	}

	public function setRollingAmplitude(int $time) : void{
		$this->getNetworkProperties()->setInt(EntityMetadataProperties::HURT_TIME, $time);
		$this->hurtTime = $time;
	}

	public function getRollingDirection() : int{
		return $this->hurtDirection;
	}

	public function setRollingDirection(int $direction) : void{
		$this->getNetworkProperties()->setInt(EntityMetadataProperties::HURT_DIRECTION, $direction);
		$this->hurtDirection = $direction;
	}

	protected function doHurtAnimation() : bool{
		// TODO: hurt animation
		$this->setRollingAmplitude(20);
		$this->setRollingDirection($this->rollingDirection ? 1 : -1);
		$this->rollingDirection = !$this->rollingDirection;
		return true;
	}

	public function onUpdate(int $currentTick) : bool{
		if($this->getRollingAmplitude() > 0){
			$this->setRollingAmplitude($this->getRollingAmplitude() - 1);
		}
		return parent::onUpdate($currentTick);
	}

	public function handleAnimatePacket(AnimatePacket $packet) : bool{
		return false;
	}

	public function isEmpty() : bool{
		return count($this->passangers) === 0;
	}

	public function getRider() : ?Entity{
		return $this->getPassenger(0);
	}

	public function getPassenger(int $index) : ?Entity{
		return $this->passangers[$index] ?? null;
	}

	/** @return Entity[] */
	public function getPassengers() : array{
		return $this->passangers;
	}

	public function setPassenger(Entity $entity, int $index) : bool{
		$ridePos = $this->getRidingPositions();
		if(!isset($ridePos[$index])) return false;
		if(isset($this->passangers[$index])){
			$this->removePassenger($this->passangers[$index]);
		}
		$this->passangers[$index] = $entity;
		self::$riders[$entity->getId()] = $this;

		$properties = $entity->getNetworkProperties();
		$properties->setGenericFlag(EntityMetadataFlags::RIDING, true);
		$properties->setGenericFlag(EntityMetadataFlags::SITTING, true);
		$properties->setVector3(EntityMetadataProperties::RIDER_SEAT_POSITION, $ridePos[$index]);
		$this->broadcastLink($entity, $index === 0? EntityLink::TYPE_RIDER : EntityLink::TYPE_PASSENGER);
		return true;
	}

	public function addPassenger(Entity $entity) : bool{
		$index = null;
		$ridePos = $this->getRidingPositions();
		for($i = 0, $len = count($ridePos); $i < $len; ++$i){
			if(!isset($this->passangers[$i])){
				$index = $i;
				break;
			}
		}
		if($index === null) return false;
		$this->setPassenger($entity, $index);
		return true;
	}

	public function removePassenger(Entity $entity) : bool{
		$index = array_search($entity, $this->passangers, true);
		if($index === false) return false;
		unset($this->passangers[$index]);
		unset(self::$riders[$entity->getId()]);
		$this->passangers = array_values($this->passangers);
		$properties = $entity->getNetworkProperties();
		$properties->setGenericFlag(EntityMetadataFlags::RIDING, false);
		$properties->setGenericFlag(EntityMetadataFlags::SITTING, false);
		$this->broadcastLink($entity, EntityLink::TYPE_REMOVE);
		return true;
	}

	protected function broadcastLink(Entity $player, int $type) : void{
		$pk = new SetActorLinkPacket();
		$pk->link = new EntityLink($this->getId(), $player->getId(), $type, true, true);
		$this->getWorld()->broadcastPacketToViewers($this->getPosition(), $pk);
	}
}
