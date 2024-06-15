<!-- resources/views/admin/device-form.blade.php -->
<div class="card">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <label for="device-serial" class="form-label">Serial Number</label>
                <input type="text" id="device-serial" class="form-control">
            </div>
            <div class="col-md-3">
                <label for="device-name" class="form-label">Device Name</label>
                <input type="text" id="device-name" class="form-control">
            </div>
            <div class="col-md-3">
                <label for="device-type" class="form-label">Device Type</label>
                <input type="text" id="device-type" class="form-control">
            </div>
            <div class="col-md-3">
                <label for="warranty-expiry" class="form-label">Warranty Expiry</label>
                <input type="date" id="warranty-expiry" class="form-control">
            </div>
        </div>
        <div class="mt-3">
            <button type="button" id="add-device-btn" class="btn btn-primary">Add Device</button>
        </div>
        <div class="mt-3">
            <ul id="device-list" class="list-group"></ul>
        </div>
    </div>
</div>

<script>
    document.getElementById('add-device-btn').addEventListener('click', function() {
        let deviceSerial = document.getElementById('device-serial').value;
        let deviceName = document.getElementById('device-name').value;
        let deviceType = document.getElementById('device-type').value;
        let warrantyExpiry = document.getElementById('warranty-expiry').value;

        let deviceItem = `<li class="list-group-item">${deviceSerial} - ${deviceName} - ${deviceType} - ${warrantyExpiry}</li>`;
        document.getElementById('device-list').insertAdjacentHTML('beforeend', deviceItem);

        // Reset the form fields
        document.getElementById('device-serial').value = '';
        document.getElementById('device-name').value = '';
        document.getElementById('device-type').value = '';
        document.getElementById('warranty-expiry').value = '';
    });

    // Store the device list in a hidden input field for form submission
    document.querySelector('form').addEventListener('submit', function(event) {
        let devices = [];
        document.querySelectorAll('#device-list li').forEach(function(item) {
            let parts = item.textContent.split(' - ');
            devices.push({
                serial_number: parts[0],
                device_name: parts[1],
                device_type: parts[2],
                warranty_expiry: parts[3]
            });
        });
        let input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'devices';
        input.value = JSON.stringify(devices);
        this.appendChild(input);
    });
</script>
