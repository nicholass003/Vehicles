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

namespace nicholass003\vehicles\event;

use nicholass003\vehicles\entity\Vehicle;
use nicholass003\vehicles\entity\vehicle\VehicleBoat;
use nicholass003\vehicles\item\ChestBoat;
use nicholass003\vehicles\utils\Utils;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\item\Boat;
use pocketmine\network\mcpe\protocol\AnimatePacket;
use pocketmine\network\mcpe\protocol\InteractPacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\MoveActorAbsolutePacket;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemOnEntityTransactionData;

class EventListener implements Listener{

    public function onPlayerQuit(PlayerQuitEvent $event) : void{
        $player = $event->getPlayer();
        if(isset(Vehicle::$riders[$id = $player->getId()])){
            Vehicle::$riders[$id]->removePassenger($player);
        }
    }

    public function onEntityDeath(EntityDeathEvent $event) : void{
        $entity = $event->getEntity();
        if(isset(Vehicle::$riders[$id = $entity->getId()])){
            Vehicle::$riders[$id]->removePassenger($entity);
        }
    }

    public function onEntityTeleport(EntityTeleportEvent $event) : void{
        $entity = $event->getEntity();
        if(isset(Vehicle::$riders[$id = $entity->getId()])){
            Vehicle::$riders[$id]->removePassenger($entity);
        }
    }

    public function onPlayerInteract(PlayerInteractEvent $event) : void{
        $player = $event->getPlayer();

        if($event->getAction() === PlayerInteractEvent::RIGHT_CLICK_BLOCK){
            $block = $event->getBlock();
            $item = $event->getItem();
            if($item instanceof Boat){
                $pos = $block->getPosition()->add(0.5, Utils::checkBlockType($block), 0.5);
                $entity = Utils::createEntityVehicle($block->getPosition()->getWorld(), $pos, lcg_value() * 360, 0, Utils::VEHICLE_TYPE_BOAT, Utils::matchBoatType($item->getType()));
                $item->pop();
                $player->getInventory()->setItemInHand($item);
                $entity->spawnToAll();
            }elseif($item instanceof ChestBoat){
                $pos = $block->getPosition()->add(0.5, Utils::checkBlockType($block), 0.5);
                $entity = Utils::createEntityVehicle($block->getPosition()->getWorld(), $pos, lcg_value() * 360, 0, Utils::VEHICLE_TYPE_CHEST_BOAT, Utils::matchBoatType($item->getType()));
                $item->pop();
                $player->getInventory()->setItemInHand($item);
                $entity->spawnToAll();
            }
        }
    }

    public function onDataPacketReceive(DataPacketReceiveEvent $event) : void{
        $player = $event->getOrigin()->getPlayer();
        if($player === null) return;

        $packet = $event->getPacket();
        if($packet instanceof InventoryTransactionPacket && $packet->trData instanceof UseItemOnEntityTransactionData && $packet->trData->getActionType() === UseItemOnEntityTransactionData::ACTION_INTERACT){
            $entity = $player->getWorld()->getEntity($packet->trData->getActorRuntimeId());
            if($entity instanceof Vehicle && !$entity->isClosed()){
                if(!$event->isCancelled()){
                    $entity->addPassenger($player);
                }
            }
            $event->cancel();
        }elseif($packet instanceof MoveActorAbsolutePacket){
            $entity = $player->getWorld()->getEntity($packet->actorRuntimeId);
            if($entity instanceof Vehicle && !$entity->isClosed()){
                $entity->absoluteMove($packet->position, $packet->yaw, $packet->pitch);
                $event->cancel();
            }
        }elseif($packet instanceof InteractPacket){
            $entity = $player->getWorld()->getEntity($packet->targetActorRuntimeId);
            if($packet->action === InteractPacket::ACTION_LEAVE_VEHICLE){
                if($entity instanceof Vehicle &&!$entity->isClosed()){
                    $entity->removePassenger($player);
                    $event->cancel();
                }
            }
        }elseif($packet instanceof AnimatePacket){
            $entity = Vehicle::$riders[$player->getId()]?? null;
            if($entity === null) return;
            if($entity instanceof Vehicle && !$entity->isClosed()){
                switch($packet->action){
                    case VehicleBoat::ACTION_ROW_RIGHT:
                    case VehicleBoat::ACTION_ROW_LEFT:
                        $entity->handleAnimatePacket($packet);
                        $event->cancel();
                        break;
                }
            }
        }
    }
}