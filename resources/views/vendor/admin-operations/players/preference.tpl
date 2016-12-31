<select class="form-control" id="preference">
    <option {{ ($preference == "default") ? 'selected=selected' : '' }} value="default">Default</option>
    <option {{ ($preference == "slim") ? 'selected=selected' : '' }} value="slim">Slim</option>
</select>
