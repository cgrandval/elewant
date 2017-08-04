<?php

namespace spec\Elewant\Herding\Model\Handlers;

use Elewant\Herding\Model\Breed;
use Elewant\Herding\Model\Commands\AbandonElePHPant;
use Elewant\Herding\Model\Commands\AdoptElePHPant;
use Elewant\Herding\Model\Events\ElePHPantWasAbandonedByHerd;
use Elewant\Herding\Model\Handlers\AbandonElePHPantHandler;
use Elewant\Herding\Model\Herd;
use Elewant\Herding\Model\HerdCollection;
use Elewant\Herding\Model\ShepherdId;
use Elewant\Tooling\PhpSpec\popAggregateEventsTrait;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Webmozart\Assert\Assert;

class AbandonElePHPantHandlerSpec extends ObjectBehavior
{

    use popAggregateEventsTrait;

    /** @var  HerdCollection */
    private $herdCollection;

    function let(HerdCollection $herdCollection)
    {
        $this->herdCollection = $herdCollection;
        $this->beConstructedWith($herdCollection);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AbandonElePHPantHandler::class);
    }

    function it_handles_abandon_elephpant()
    {
        $herd = Herd::form(ShepherdId::fromString('00000000-0000-0000-0000-000000000000'), 'Herd name');
        $herdId = $herd->herdId();
        $herd->adoptElePHPant(Breed::whiteDpcRegular());

        $command = AbandonElePHPant::byHerd($herdId->toString(), Breed::WHITE_DPC_REGULAR);

        $this->herdCollection->get($herdId)->willReturn($herd);
        $this->herdCollection->save(Argument::type(Herd::class))->shouldBeCalled();
        $this->__invoke($command);

        $events = $this->popRecordedEvent($herd);

        Assert::count($events, 3);
        Assert::isInstanceOf($events[2], ElePHPantWasAbandonedByHerd::class);

        $payload = $events[2]->payload();
        Assert::same($payload['breed'], Breed::WHITE_DPC_REGULAR);
    }

}