<?php

namespace Tests\Unit;

use OGame\Planet;
use OGame\Services\PlanetService;
use OGame\Services\PlayerService;
use OGame\UserTech;
use PHPUnit\Framework\TestCase;

class HighscoreCalculationTest extends TestCase
{
    protected $planetService;
    protected $playerService;

    /**
     * Set up common test components.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Initialize empty playerService object
        $this->playerService = app()->make(PlayerService::class, ['player_id' => 0]);
        // Initialize the planet service before each test
        $this->planetService = app()->make(PlanetService::class, ['player' => $this->playerService, 'planet_id' => 0]);
    }

    /**
     * Helper method to create a planet model with preconfigured levels.
     */
    protected function createAndConfigurePlanetModel(array $attributes): void
    {
        // Create fake planet eloquent model with additional attributes
        $planetModelFake = Planet::factory()->make($attributes);
        // Set the fake model to the planet service
        $this->planetService->setPlanet($planetModelFake);
    }

    /**
     * Helper method to create a user tech model with preconfigured levels.
     */
    protected function createAndConfigureUserTechModel(array $attributes): void
    {
        // Create fake user tech eloquent model with additional attributes
        $userTechModelFake = UserTech::factory()->make($attributes);
        // Set the fake model to the planet service
        $this->playerService->setUserTech($userTechModelFake);
    }

    /**
     * Test that the planet score is calculated correctly based on building levels.
     */
    public function testBuildingScore(): void
    {
        $this->createAndConfigurePlanetModel([
            'metal_mine' => 10,
            'crystal_mine' => 10,
            'solar_plant' => 10,
        ]);

        // Check that the score is calculated correctly based on spent resouces for above.
        // buildings = 33k = 33
        $this->assertEquals(33, $this->planetService->getPlanetScore());
    }

    /**
     * Test that the planet score is calculated correctly based on unit amounts.
     */
    public function testUnitScore(): void
    {
        $this->createAndConfigurePlanetModel([
            'light_fighter' => 10,
            'battle_ship' => 10,
        ]);

        // Check that the score is calculated correctly based on spent resouces for above.
        // light fighter = 4k * 10 = 40
        // battleship = 60k * 10 = 600
        $this->assertEquals(640, $this->planetService->getPlanetScore());
    }

    /**
     * Test that the planet score is calculated correctly based on building levels and unit count combined.
     */
    public function testBuildingUnitScore(): void
    {
        $this->createAndConfigurePlanetModel([
            'metal_mine' => 10,
            'crystal_mine' => 10,
            'solar_plant' => 10,
            'light_fighter' => 10,
            'battle_ship' => 10,
        ]);

        // Check that the score is calculated correctly based on spent resouces for above.
        // light fighter = 4k * 10 = 40
        // battleship = 60k * 10 = 600
        // buildings = 33k = 33
        $this->assertEquals(673, $this->planetService->getPlanetScore());
    }

    /**
     * Test that the player score is calculated correctly based on research levels.
     */
    public function testPlayerResearchScore(): void
    {
        $this->createAndConfigureUserTechModel([
            'laser_technology' => 3,
            'astrophysics' => 4,
            'shielding_technology' => 5,
        ]);

        // Check that the score is calculated correctly based on spent resouces for above.
        // laser_technology = 0.3 + 0.6 + 1.2 = 2.1
        // astrophysics = 16 + 28 + 49.1 + 85.7 = 178.8
        // shielding_technology = 0.8 + 1.6 + 3.2 + 6.4 + 12.8 = 24.8
        // Total = 205.7
        $this->assertEquals(205, $this->playerService->getResearchScore());
    }

    /**
     * Test that the planet score is calculated correctly based on building levels and unit count combined.
     */
    public function testEconomyScore(): void
    {
        $this->createAndConfigurePlanetModel([
            'metal_mine' => 10,
            'small_cargo' => 10,
            'battle_ship' => 10, // This should not affect economy points as this is not a civil ship.
        ]);

        // Check that the point count is calculated correctly based on spent resources for above.
        $this->assertEquals(28, $this->planetService->getPlanetScoreEconomy());
    }

    /**
     * Test that the planet score is calculated correctly based on building levels and unit count combined.
     */
    public function testMilitaryPoints(): void
    {
        $this->createAndConfigurePlanetModel([
            'metal_mine' => 10, // This should not affect the military points.
            'small_cargo' => 10, // This should be calculcated as 50% because it is a civil ship.
            'light_fighter' => 10, // 100%
            'battle_ship' => 10, // 100%
        ]);

        // Check that the score is correctly calculated according to the military score formula.
        $this->assertEquals(660, $this->planetService->getPlanetMilitaryScore());
    }
}
