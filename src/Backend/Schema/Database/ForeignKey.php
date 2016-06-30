<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2016 Christoph Kappestein <k42b3.x@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Fusio\Impl\Backend\Schema\Database;

use PSX\Schema\Property;
use PSX\Schema\SchemaAbstract;

/**
 * ForeignKey
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class ForeignKey extends SchemaAbstract
{
    public function getDefinition()
    {
        $sb = $this->getSchemaBuilder('foreignKey');
        $sb->string('name');
        $sb->arrayType('columns')
            ->setPrototype(new Property\StringType());
        $sb->string('foreignTable');
        $sb->arrayType('foreignColumns')
            ->setPrototype(new Property\StringType());

        return $sb->getProperty();
    }
}
