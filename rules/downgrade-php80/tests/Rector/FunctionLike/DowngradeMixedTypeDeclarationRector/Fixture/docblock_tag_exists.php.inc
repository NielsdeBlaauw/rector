<?php

namespace Rector\DowngradePhp80\Tests\Rector\FunctionLike\DowngradeMixedTypeDeclarationRector\Fixture;

class DocblockTagExists {
    /**
     * This property is the best one
     * @param mixed $anything
     */
    public function someFunction(mixed $anything)
    {
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp80\Tests\Rector\FunctionLike\DowngradeMixedTypeDeclarationRector\Fixture;

class DocblockTagExists {
    /**
     * This property is the best one
     * @param mixed $anything
     */
    public function someFunction($anything)
    {
    }
}

?>
