/* ---

	Copyright (C) 2008 Frank Smit
	http://code.google.com/p/shinobu/

	This file is part of Shinobu.

	Shinobu is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	Shinobu is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with Shinobu. If not, see <http://www.gnu.org/licenses/>.

---  */

window.addEvent('domready', function()
{
	// Ask for confirmation
	$$('a.confirm').addEvent('click', function(event)
	{
		if (!confirm('Are you sure?'))
		{
			event.stop();
		}
	});
	
	// Open link in new window
	$$('a[rel=external]').addEvent('click', function()
	{
		this.set('target', 'external');
	});
});
