<?php

namespace Tests\Unit;

use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Services\ProductService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductsServiceTest extends TestCase
{
    protected ProductService $productService;
    protected ProductRepositoryInterface $productRepositoryMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->productRepositoryMock = $this->createMock(ProductRepositoryInterface::class);
        $this->productService = new ProductService($this->productRepositoryMock);
    }

    /**
     * Test the validateInteger method of the ProductService class.
     *
     * This test method verifies that the validateInteger function correctly processes various types of input:
     * 1. Valid numeric strings are converted to integer values.
     * 2. Invalid values, including negative numbers, non-numeric strings, empty strings, and null,
     *    return a default integer value of 0.
     *
     * - The first assertion checks that a valid numeric string ('5') is converted to 5.
     * - The second assertion ensures that a negative numeric string ('-1') is treated as invalid, returning 0.
     * - The third assertion verifies that a non-numeric string ('string') is invalid and returns 0.
     * - The fourth assertion confirms that an empty string ('') is handled as invalid, returning 0.
     * - The fifth assertion checks that null input is treated as invalid and also returns 0.
     */
    #[Test]
    public function testValidateInteger()
    {
        // Test case 1: A valid numeric string should return its integer equivalent.
        $this->assertSame(5, $this->productService->validateInteger('5'));

        // Test case 2: A negative numeric string should return 0, as negative values are considered invalid.
        $this->assertSame(0, $this->productService->validateInteger('-1'));

        // Test case 3: A non-numeric string should return 0, indicating invalid input.
        $this->assertSame(0, $this->productService->validateInteger('string'));

        // Test case 4: An empty string should return 0, handling empty input as invalid.
        $this->assertSame(0, $this->productService->validateInteger(''));

        // Test case 5: Null input should return 0, treating null as invalid input.
        $this->assertSame(0, $this->productService->validateInteger(null));
    }


    /**
     * Test the validatePrice method of the ProductService class.
     *
     * This test verifies that:
     * 1. Numeric strings are converted to valid float values.
     * 2. Invalid values (negative numbers, non-numeric strings, empty strings, or null) are handled
     *    by returning a default value of 0.0.
     *
     * - The first assertion checks that a valid numeric string ('5') is converted to 5.0.
     * - The second assertion checks that a negative value ('-1') is treated as invalid, returning 0.0.
     * - The third assertion verifies that a non-numeric string ('string') returns 0.0.
     * - The fourth assertion checks that an empty string ('') returns 0.0.
     * - The fifth assertion confirms that null input returns 0.0.
     */
    #[Test]
    public function testValidatePrice()
    {
        // Test case 1: A valid numeric string should be converted to its float equivalent.
        $this->assertSame(5.0, $this->productService->validatePrice('5'));

        // Test case 2: A negative number string should return 0.0 as it's considered invalid.
        $this->assertSame(0.0, $this->productService->validatePrice('-1'));

        // Test case 3: A non-numeric string should return 0.0, indicating invalid input.
        $this->assertSame(0.0, $this->productService->validatePrice('string'));

        // Test case 4: An empty string should return 0.0, treating it as invalid.
        $this->assertSame(0.0, $this->productService->validatePrice(''));

        // Test case 5: Null input should also return 0.0, handling null as invalid input.
        $this->assertSame(0.0, $this->productService->validatePrice(null));
    }

    /**
     * Test the validateAndSanitizeString method of the ProductService class.
     *
     * This test ensures that:
     * 1. Leading and trailing whitespace is removed from the input string.
     * 2. Special characters are properly sanitized to prevent security issues such as XSS attacks.
     *
     * - The first assertion checks that a string with whitespace (" test ") is trimmed to "test".
     * - The second assertion checks that a string with a double quote (`"`) is sanitized to its HTML entity (`&quot;`).
     */
    #[Test]
    public function testValidateAndSanitizeString()
    {
        // Test case 1: Check that the method removes leading and trailing whitespace.
        $this->assertSame("test", $this->productService->validateAndSanitizeString(' test '));

        // Test case 2: Check that the method converts double quotes to HTML entities to prevent XSS attacks.
        $this->assertSame("test &quot;", $this->productService->validateAndSanitizeString('test " '));
    }

    /**
     * Test the sanitizeAndDecodeVariations method of the ProductService class.
     *
     * This test verifies that the sanitizeAndDecodeVariations function:
     * Correctly decodes JSON strings containing variations in Arabic.
     *
     * - The first test case checks a valid JSON string with Arabic characters to ensure proper decoding.
     * - The second test case uses another malformed JSON input to verify that an empty array is returned.
     * - The third test case verifies that an empty string input returns an empty array.
     */
    #[Test]
    public function testSanitizeAndDecodeVariations(): void
    {
        // Test case 1: Valid JSON with Arabic characters, checking correct decoding.
        $variationsJson1 = '[{""name"":""\u0627\u0644\u062d\u062c\u0645"",""value"":""\u0663\u0660 \u062c\u0631\u0639\u0647""},{""name"":""\u0627\u0644\u0646\u0643\u0647\u0647"",""value"":""\u0627\u0644\u062a\u0648\u062a \u0627\u0644\u0623\u0632\u0631\u0642, \u0627\u0644\u0641\u0648\u0627\u0643\u0647, \u0627\u0644\u0639\u0646\u0628""}]';
        $expected1 = [
            ['name' => 'الحجم', 'value' => '٣٠ جرعه'],
            ['name' => 'النكهه', 'value' => 'التوت الأزرق, الفواكه, العنب']
        ];
        $result1 = $this->productService->sanitizeAndDecodeVariations($variationsJson1);
        $this->assertSame($expected1, $result1);


        // Test case 2: Another malformed JSON input, expecting an empty array as output.
        $variationsJson2 = '[{name"":""\u0627\u0644\u062d\u062c\u0645""]';
        $result2 = $this->productService->sanitizeAndDecodeVariations($variationsJson2);
        $this->assertSame([], $result2);

        // Test case 3: Empty input string should return an empty array.
        $result3 = $this->productService->sanitizeAndDecodeVariations('');
        $this->assertSame([], $result3);
    }
}
