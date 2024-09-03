<?php

namespace OGame\Services;

use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use OGame\GameObjects\Models\Enums\GameObjectType;
use OGame\GameObjects\Models\Units\UnitCollection;
use OGame\Models\DebrisField;
use OGame\Models\FleetMission;
use OGame\Models\Planet;
use OGame\Models\Planet\Coordinate;
use OGame\Models\Resource;
use OGame\Models\Resources;
use RuntimeException;
use Throwable;
use OGame\Models\PlanetCoordinates;

/**
 * Class DebrisFieldService.
 *
 * Debris field object.
 *
 * @package OGame\Services
 */
class DebrisFieldService
{
    /**
     * The debris field object model.
     *
     * @var DebrisField
     */
    private DebrisField $debrisField;

    /**
     * The planet object that the debris field belongs to. This can be null if the debris field is not
     * associated with a planet.
     *
     * @var PlanetService
     */
    private PlanetService $planet;

    /**
     * Load debris field by coordinate.
     *
     * @param Coordinate $coordinate
     * The coordinate of the debris field.
     *
     * @return void
     */
    public function loadByCoordinates(Coordinate $coordinate): void
    {
        // Fetch planet model
        $debrisField = DebrisField::where('galaxy', $coordinate->galaxy)
            ->where('system', $coordinate->system)
            ->where('planet', $coordinate->position)
            ->first();

        // TODO: improve null check as debris field model can be non-existent.
        if ($debrisField !== null) {
            $this->debrisField = $debrisField;
        } else {
            $this->debrisField = new DebrisField();
        }
    }

    /**
     * Get the coordinates of the debris field.
     */
    public function getCoordinates(): Coordinate
    {
        return new Coordinate($this->debrisField->galaxy, $this->debrisField->system, $this->debrisField->planet);
    }

    /**
     * Reloads the debris field object from the database.
     *
     * @return void
     */
    public function reload(): void
    {
        $this->loadByCoordinates($this->getCoordinates());
    }

    /**
     * Returns the resources of the debris field.
     *
     * @return Resources
     */
    public function getResources(): Resources
    {
        if ($this->debrisField->metal === null) {
            return new Resources(0, 0, 0, 0);
        }

        return new Resources($this->debrisField->metal, $this->debrisField->crystal, $this->debrisField->deuterium, 0);
    }

    /**
     * Load an existing debris field or create a new one in memory for the given coordinates.
     *
     * @param Coordinate $coordinates
     */
    public function loadOrCreateForCoordinates(Coordinate $coordinates): void
    {
        $debrisField = DebrisField::where('galaxy', $coordinates->galaxy)
            ->where('system', $coordinates->system)
            ->where('planet', $coordinates->position)
            ->first();

        if (!$debrisField) {
            $debrisField = new DebrisField();
            $debrisField->galaxy = $coordinates->galaxy;
            $debrisField->system = $coordinates->system;
            $debrisField->planet = $coordinates->position;
            $debrisField->metal = 0;
            $debrisField->crystal = 0;
        }

        $this->debrisField = $debrisField;
    }

    /**
     * Append resources to an existing debris field.
     *
     * @param Resources $resources
     * @return void
     */
    public function appendResources(Resources $resources): void
    {
        $this->debrisField->metal += $resources->metal->get();
        $this->debrisField->crystal += $resources->crystal->get();
        $this->debrisField->deuterium += $resources->deuterium->get();
    }

    /**
     * Save the debris field to the database.
     *
     * @return void
     */
    public function save(): void
    {
        $this->debrisField->save();
    }
}
