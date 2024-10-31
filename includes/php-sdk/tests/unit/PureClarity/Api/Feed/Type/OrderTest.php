<?php
/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace PureClarity\Tests\Unit\Api\Feed\Type;

use Exception;
use Mockery\MockInterface;
use PureClarity\Api\Feed\Feed;
use PureClarity\Api\Feed\Transfer;
use PureClarity\Api\Feed\Type\Order;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

/**
 * Class OrderTest
 *
 * Unit Test for \PureClarity\Api\Delta\Type\Order
 *
 * @see \PureClarity\Api\Delta\Type\Order
 */
class OrderTest extends MockeryTestCase
{
    /** @var string $accessKey */
    const ACCESS_KEY = 'ABCDEFGHI';

    /** @var string $secretKey */
    const SECRET_KEY = 'ZYXWVUTQR';

    /** @var string $region */
    const REGION = 1;

    /**
     * Tests that the class instantiates correctly
     */
    public function testInstance()
    {
        $subject = new Order(
            self::ACCESS_KEY,
            self::SECRET_KEY,
            self::REGION
        );

        $this->assertInstanceOf(Order::class, $subject);
        $this->assertInstanceOf(Feed::class, $subject);
    }

    /**
     * Tests calling create sends the correct type-specific feed start
     */
    public function testCreate()
    {
        $this->mockTransfer('create', 'OrderId,UserId,Email,DateTimeStamp,ProdCode,Quantity,UnityPrice,LinePrice');

        $subject = new Order(
            self::ACCESS_KEY,
            self::SECRET_KEY,
            self::REGION
        );

        $error = '';
        try {
            $subject->start();
        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        $this->assertEquals(
            '',
            $error
        );
    }

    /**
     * Tests attempting to append bad data returns the correct type-specific errors
     */
    public function testAppendInvalid()
    {
        $this->mockTransfer(false);

        $subject = new Order(
            self::ACCESS_KEY,
            self::SECRET_KEY,
            self::REGION
        );

        $error = '';
        try {
            $subject->append([[]]);
        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        $this->assertEquals(
            'Missing OrderID|Missing UserId|Missing Email|Missing DateTime|Missing ProdCode|Missing Quantity|'
            . 'Missing UnitPrice|Missing LinePrice',
            $error
        );
    }

    /**
     * Tests attempting to append bad data returns the correct type-specific errors
     */
    public function testAppendEmpty()
    {
        $this->mockTransfer(false);

        $data = [
            'OrderID' => '',
            'UserId' => '',
            'Email' => '',
            'DateTime' => '',
            'ProdCode' => '',
            'Quantity' => '',
            'UnitPrice' => '',
            'LinePrice' => ''
        ];

        $subject = new Order(
            self::ACCESS_KEY,
            self::SECRET_KEY,
            self::REGION
        );

        $error = '';
        try {
            $subject->append([$data]);
        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        $this->assertEquals(
            'Missing data for OrderID|Missing data for ProdCode|Missing data for Quantity|'
            . 'Missing data for UnitPrice|Missing data for LinePrice',
            $error
        );
    }

    /**
     * Tests that a single append does not send data (as default page size is 50)
     */
    public function testAppendValidNotSent()
    {
        $data = [
            'OrderID' => 1,
            'UserId' => 1,
            'Email' => 'email@example.com',
            'DateTime' => '2020-01-01 00:00:00',
            'ProdCode' => 'ABCDEF',
            'Quantity' => '2',
            'UnitPrice' => '7.5',
            'LinePrice' => '15'
        ];

        $this->mockTransfer(false);

        $subject = new Order(
            self::ACCESS_KEY,
            self::SECRET_KEY,
            self::REGION
        );

        $error = '';
        try {
            $subject->append([$data]);
        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        $this->assertEquals(
            '',
            $error
        );
    }

    /**
     * Tests that when enough appends are done, the append method is called on the transfer object
     */
    public function testAppendValidDefaultPageSize()
    {
        $data = $this->generateData();

        $this->mockTransfer('append', $data['string']);

        $subject = new Order(
            self::ACCESS_KEY,
            self::SECRET_KEY,
            self::REGION
        );

        $error = '';
        try {
            foreach ($data['array'] as $row) {
                $subject->append([$row]);
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        $this->assertEquals(
            '',
            $error
        );
    }

    /**
     * Tests that when setting the page size to a different value, the append method is called on the transfer object
     * at the correct point
     */
    public function testAppendValidNonDefaultPageSize()
    {
        $data = $this->generateData(10);

        $this->mockTransfer('append', $data['string']);

        $subject = new Order(
            self::ACCESS_KEY,
            self::SECRET_KEY,
            self::REGION
        );

        $subject->setPageSize(10);

        $error = '';
        try {
            foreach ($data['array'] as $row) {
                $subject->append([$row]);
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        $this->assertEquals(
            '',
            $error
        );
    }

    /**
     * Tests that when setting the page size to a different value, the append method is called on the transfer object
     * at the correct points if more than one page is sent
     */
    public function testAppendValidNonDefaultPageSizeMultiPage()
    {
        $page1Data = $this->generateData(10);

        $transfer = $this->mockTransfer('append', $page1Data['string']);

        $page2Data = $this->generateData(10, 2);
        $transfer->shouldReceive('append')
            ->with($page2Data['string'])
            ->times(1);

        $page3Data = $this->generateData(10, 3);
        $transfer->shouldReceive('append')
            ->with($page3Data['string'])
            ->times(1);

        $page4Data = $this->generateData(10, 4);
        $transfer->shouldReceive('append')
            ->with($page4Data['string'])
            ->times(1);

        $subject = new Order(
            self::ACCESS_KEY,
            self::SECRET_KEY,
            self::REGION
        );

        $subject->setPageSize(10);

        $error = '';
        try {
            foreach ($page1Data['array'] as $row) {
                $subject->append([$row]);
            }
            foreach ($page2Data['array'] as $row) {
                $subject->append([$row]);
            }
            foreach ($page3Data['array'] as $row) {
                $subject->append([$row]);
            }
            foreach ($page4Data['array'] as $row) {
                $subject->append([$row]);
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        $this->assertEquals(
            '',
            $error
        );
    }

    /**
     * Tests calling end sends the correct type-specific feed end in the close function
     */
    public function testEnd()
    {
        $this->mockTransfer('close', '');

        $subject = new Order(
            self::ACCESS_KEY,
            self::SECRET_KEY,
            self::REGION
        );

        $error = '';
        try {
            $subject->end();
        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        $this->assertEquals(
            '',
            $error
        );
    }

    /**
     * Tests calling end with unset data sends an append and
     * then the correct type-specific feed end in the close function
     */
    public function testEndWithAppend()
    {
        $transfer = $this->mockTransfer('close', '');

        $pageData = $this->generateData(1);

        $transfer->shouldReceive('append')
            ->with($pageData['string'])
            ->times(1);

        $subject = new Order(
            self::ACCESS_KEY,
            self::SECRET_KEY,
            self::REGION
        );

        $error = '';
        try {
            foreach ($pageData['array'] as $row) {
                $subject->append([$row]);
            }
            $subject->end();
        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        $this->assertEquals(
            '',
            $error
        );
    }

    /**
     * Mocks the Transfer class
     *
     * @see \PureClarity\Api\Feed\Transfer
     * @param string $method - method to mock (will vary depending on test)
     * @param string $data - data that the method should expect to see
     * @param string $exception - optional exception to throw
     * @return MockInterface
     */
    private function mockTransfer($method, $data = '', $exception = '')
    {
        $transfer = m::mock('overload:' . Transfer::class);

        if ($method) {
            if (empty($exception)) {
                $transfer->shouldReceive($method)
                    ->with($data)
                    ->times(1);
            } else {
                $transfer->shouldReceive($method)
                    ->with($data)
                    ->times(1)
                    ->andThrow(new Exception($exception));
            }
        }

        return $transfer;
    }

    /**
     * Generates Dummy data to use with the append function
     *
     * @param int $pageSize - page size (i.e. num of rows to generate)
     * @param int $pageNum - page number (so indexing uses right value)
     * @return mixed[]
     */
    private function generateData($pageSize = 50, $pageNum = 1)
    {
        $baseData = [
            'OrderID' => 1,
            'UserId' => 1,
            'Email' => 'email@example.com',
            'DateTime' => '2020-01-01 00:00:00',
            'ProdCode' => 'ABCDEF',
            'Quantity' => '2',
            'UnitPrice' => '7.5',
            'LinePrice' => '15'
        ];

        $data = [];
        $dataString = '';

        $start = 1;
        if ($pageNum > 1) {
            $start = ($pageSize * ($pageNum - 1) + 1);
        }

        $end = $pageSize;
        if ($pageNum > 1) {
            $end = $pageSize * $pageNum;
        }

        for ($i = $start; $i <= $end; $i++) {
            $baseData['OrderID'] = $i;
            $dataString .= PHP_EOL . $baseData['OrderID'] . ',' .
                $baseData['UserId'] . ',' .
                $baseData['Email'] . ',' .
                $baseData['DateTime'] . ',' .
                $baseData['ProdCode'] . ',' .
                $baseData['Quantity'] . ',' .
                $baseData['UnitPrice'] . ',' .
                $baseData['LinePrice'];
            $data[] = $baseData;
        }

        return [
            'array' => $data,
            'string' => $dataString
        ];
    }
}
